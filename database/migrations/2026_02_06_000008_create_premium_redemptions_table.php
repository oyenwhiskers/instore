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
        Schema::create('premium_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hourly_report_id')->constrained('hourly_reports')->cascadeOnDelete();
            $table->unsignedTinyInteger('tier');
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_redemptions');
    }
};
