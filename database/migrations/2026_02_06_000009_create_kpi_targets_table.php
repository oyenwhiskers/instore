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
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoter_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('period_type');
            $table->date('period_start');
            $table->decimal('target_sales_amount', 10, 2)->default(0);
            $table->unsignedInteger('target_engagements')->default(0);
            $table->unsignedInteger('target_samplings')->default(0);
            $table->unsignedInteger('target_premium_tier1')->default(0);
            $table->unsignedInteger('target_premium_tier2')->default(0);
            $table->timestamps();

            $table->unique(['promoter_user_id', 'period_type', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_targets');
    }
};
