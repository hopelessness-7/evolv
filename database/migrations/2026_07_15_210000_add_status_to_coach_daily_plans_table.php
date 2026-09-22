<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coach_daily_plans', function (Blueprint $table) {
            $table->string('status', 32)->default('ready')->after('source');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('coach_daily_plans', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropColumn('status');
        });
    }
};
