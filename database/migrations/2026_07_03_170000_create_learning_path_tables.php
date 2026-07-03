<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('track', 32);
            $table->string('status', 32)->default('active');
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('learning_plan_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('learning_plans')->cascadeOnDelete();
            $table->foreignId('node_id')->constrained('knowledge_nodes')->cascadeOnDelete();
            $table->unsignedSmallInteger('order_in_plan');
            $table->string('status', 32)->default('locked');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'node_id']);
            $table->unique(['plan_id', 'order_in_plan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_plan_steps');
        Schema::dropIfExists('learning_plans');
    }
};
