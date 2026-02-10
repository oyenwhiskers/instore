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
        Schema::create('event_promoter_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('promoter_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('target_sales_amount', 12, 2)->nullable();
            $table->unsignedInteger('target_engagements')->nullable();
            $table->unsignedInteger('target_samplings')->nullable();
            $table->unsignedInteger('target_premium_tier1')->nullable();
            $table->unsignedInteger('target_premium_tier2')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'promoter_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_promoter_kpis');
    }
};
