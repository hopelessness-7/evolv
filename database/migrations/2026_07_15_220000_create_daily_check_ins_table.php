<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('plan_date');
            $table->unsignedTinyInteger('energy');
            $table->unsignedTinyInteger('focus');
            $table->unsignedTinyInteger('practice_ready');
            $table->string('note', 1000)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'plan_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_check_ins');
    }
};
