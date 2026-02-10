<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('premium_redemptions', function (Blueprint $table) {
            $table->foreignId('premium_id')
                ->nullable()
                ->after('hourly_report_id')
                ->constrained('premiums')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('premium_redemptions', function (Blueprint $table) {
            $table->dropForeign(['premium_id']);
            $table->dropColumn('premium_id');
        });
    }
};
