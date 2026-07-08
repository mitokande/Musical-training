<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            // Tier + moderation state machine
            $table->string('tier', 20)->default('basic')->after('user_id'); // basic | premium
            $table->string('slug')->nullable()->unique()->after('tier');
            $table->string('status', 30)->default('draft')->after('slug'); // draft | submitted_for_review | approved | rejected | suspended | archived
            $table->boolean('admin_forced_private')->default(false)->after('status');

            // General information
            $table->string('headline', 160)->nullable();
            $table->string('expertise')->nullable();
            $table->string('cover_image_path', 500)->nullable();
            $table->text('about')->nullable();
            $table->text('teaching_methodology')->nullable();
            $table->json('teaching_formats')->nullable(); // ['online','in_person','hybrid']
            $table->json('lesson_types')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('public_email')->nullable();
            $table->boolean('show_email')->default(false);
            $table->string('public_phone', 30)->nullable();
            $table->boolean('show_phone')->default(false);

            // Music profile
            $table->string('primary_instrument', 100)->nullable();
            $table->string('education_status', 100)->nullable();
            $table->text('certificates')->nullable();
            $table->text('workshops')->nullable();
            $table->text('masterclasses')->nullable();
            $table->text('teaching_experience')->nullable();
            $table->json('genres')->nullable();
            $table->json('expertise_areas')->nullable();
            $table->json('age_groups')->nullable();
            $table->json('skill_levels')->nullable();
            $table->json('teaching_languages')->nullable();

            // SEO
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();

            // Lifecycle timestamps + moderation
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->unsignedInteger('view_count')->default(0);

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'tier', 'slug', 'status', 'admin_forced_private',
                'headline', 'expertise', 'cover_image_path', 'about', 'teaching_methodology',
                'teaching_formats', 'lesson_types', 'country', 'city',
                'public_email', 'show_email', 'public_phone', 'show_phone',
                'primary_instrument', 'education_status', 'certificates', 'workshops',
                'masterclasses', 'teaching_experience', 'genres', 'expertise_areas',
                'age_groups', 'skill_levels', 'teaching_languages',
                'seo_title', 'seo_description',
                'submitted_at', 'approved_at', 'published_at', 'rejected_at', 'suspended_at',
                'rejection_reason', 'view_count',
            ]);
        });
    }
};
