<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('knowledge_nodes')->cascadeOnDelete();
            $table->unsignedInteger('version_no')->default(1);
            $table->foreignId('parent_version_id')->nullable()->constrained('content_versions')->nullOnDelete();
            $table->string('status', 32)->default('draft');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['node_id', 'version_no']);
            $table->index(['node_id', 'status']);
        });

        Schema::create('content_atoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('content_versions')->cascadeOnDelete();
            $table->string('kind', 32);
            $table->text('body_md');
            $table->json('meta')->nullable();
            $table->unsignedSmallInteger('order_in_version')->default(0);
            $table->string('qdrant_point_id')->nullable();
            $table->timestamps();

            $table->index(['version_id', 'order_in_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_atoms');
        Schema::dropIfExists('content_versions');
    }
};
