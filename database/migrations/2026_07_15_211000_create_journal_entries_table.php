<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 32)->default('note');
            $table->text('body');
            $table->string('node_slug', 128)->nullable();
            $table->date('plan_date')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'plan_date']);
            $table->index(['user_id', 'node_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
