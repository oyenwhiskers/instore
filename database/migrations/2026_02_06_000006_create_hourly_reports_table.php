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
        Schema::create('hourly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->date('report_date');
            $table->unsignedTinyInteger('report_hour');
            $table->decimal('total_sales_amount', 10, 2)->default(0);
            $table->unsignedInteger('engagements_count')->default(0);
            $table->unsignedInteger('samplings_count')->default(0);
            $table->timestamps();

            $table->unique(['promoter_user_id', 'report_date', 'report_hour']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hourly_reports');
    }
};
