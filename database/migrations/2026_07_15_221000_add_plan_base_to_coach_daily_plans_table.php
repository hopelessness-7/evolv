<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coach_daily_plans', function (Blueprint $table) {
            $table->json('plan_base')->nullable()->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('coach_daily_plans', function (Blueprint $table) {
            $table->dropColumn('plan_base');
        });
    }
};
