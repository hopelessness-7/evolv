<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('timezone')->default('UTC');
            $table->unsignedSmallInteger('daily_minutes')->default(30);
            $table->json('enabled_pillars')->nullable();
            $table->json('facets')->nullable();
            $table->timestamp('core_completed_at')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('onboarding_questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64);
            $table->string('version', 16);
            $table->string('pillar', 32)->nullable();
            $table->string('tier', 16);
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('schema');
            $table->json('prompt_templates')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['code', 'version']);
            $table->index(['code', 'is_current']);
            $table->index(['pillar', 'tier']);
        });

        Schema::create('onboarding_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('questionnaire_id')->constrained('onboarding_questionnaires')->restrictOnDelete();
            $table->string('questionnaire_code', 64);
            $table->string('questionnaire_version', 16);
            $table->string('status', 32)->default('in_progress');
            $table->json('answers')->nullable();
            $table->json('interpreted')->nullable();
            $table->json('composed_prompts')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'questionnaire_code']);
        });

        Schema::create('onboarding_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('onboarding_sessions')->nullOnDelete();
            $table->string('event', 64);
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'event']);
            $table->index(['session_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_analytics_events');
        Schema::dropIfExists('onboarding_sessions');
        Schema::dropIfExists('onboarding_questionnaires');
        Schema::dropIfExists('user_profiles');
    }
};
