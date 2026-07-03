<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('track', 32);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('status', 32)->default('draft');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['track', 'status']);
        });

        Schema::create('knowledge_edges', function (Blueprint $table) {
            $table->foreignId('from_node_id')->constrained('knowledge_nodes')->cascadeOnDelete();
            $table->foreignId('to_node_id')->constrained('knowledge_nodes')->cascadeOnDelete();
            $table->string('kind', 32);
            $table->primary(['from_node_id', 'to_node_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_edges');
        Schema::dropIfExists('knowledge_nodes');
    }
};
