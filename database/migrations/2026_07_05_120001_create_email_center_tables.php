<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('html_body');
            $table->longText('text_body')->nullable();
            // transactional mail ignores marketing suppressions/unsubscribes
            $table->string('category')->default('marketing'); // marketing|transactional
            $table->json('variables')->nullable(); // documented placeholders
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->longText('custom_html')->nullable(); // used when no template selected
            $table->json('segment')->nullable();
            $table->string('status')->default('draft'); // draft|scheduled|sending|sent|cancelled|failed
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('opened_count')->default(0);
            $table->unsignedInteger('clicked_count')->default(0);
            $table->unsignedInteger('bounced_count')->default(0);
            $table->unsignedInteger('complained_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('email_automations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // welcome, first_exercise_reminder, ...
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->boolean('enabled')->default(false);
            $table->json('config')->nullable(); // delay_hours, cooldown_days, thresholds...
            $table->unsignedInteger('send_count')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });

        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('tracking_token')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_email')->index();
            $table->foreignId('campaign_id')->nullable()->constrained('email_campaigns')->nullOnDelete();
            $table->foreignId('automation_id')->nullable()->constrained('email_automations')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->string('email_type')->default('campaign'); // transactional|campaign|automation|test|support_reply
            $table->string('subject');
            $table->string('ses_message_id')->nullable()->index();
            $table->string('status')->default('queued'); // queued|sent|delivered|opened|clicked|bounced|complained|failed|suppressed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->text('error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['campaign_id', 'status']);
            $table->index(['user_id', 'email_type', 'created_at']);
        });

        Schema::create('email_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_message_id')->nullable()->constrained('email_messages')->nullOnDelete();
            $table->string('ses_message_id')->nullable()->index();
            $table->string('event_type'); // sent|delivered|opened|clicked|bounced|complained|rejected|rendering_failed|delivery_delayed|unsubscribed
            $table->string('recipient_email')->nullable()->index();
            $table->string('source')->default('ses'); // ses|internal
            $table->timestamp('occurred_at')->nullable();
            $table->json('metadata')->nullable();
            // sha256 over the raw SES event payload — blocks duplicate SNS deliveries
            $table->string('dedup_hash', 64)->unique();
            $table->timestamps();
            $table->index(['event_type', 'occurred_at']);
        });

        Schema::create('email_suppressions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason'); // hard_bounce|soft_bounce|complaint|unsubscribe|manual
            $table->string('source')->nullable(); // ses_event|admin|user
            $table->text('notes')->nullable();
            $table->timestamp('suppressed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_suppressions');
        Schema::dropIfExists('email_events');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('email_automations');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_templates');
    }
};
