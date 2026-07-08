<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            // normalized subject (Re:/Fwd: stripped) used as a threading fallback
            $table->string('subject_key')->index();
            $table->string('contact_email')->index();
            $table->string('contact_name')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('open'); // open|pending|closed
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamps();
            $table->index(['status', 'last_message_at']);
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('support_conversations')->cascadeOnDelete();
            $table->string('direction'); // inbound|outbound
            $table->string('message_id')->nullable()->index(); // RFC 5322 Message-ID
            $table->string('in_reply_to')->nullable();
            $table->text('references')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email');
            $table->string('to_email');
            $table->string('subject');
            $table->longText('plain_text_body')->nullable();
            $table->longText('html_body_sanitized')->nullable();
            $table->json('attachment_metadata')->nullable();
            $table->foreignId('sent_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ses_message_id')->nullable(); // outbound replies
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_conversations');
    }
};
