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
        Schema::table('event_premium', function (Blueprint $table) {
            $table->string('mechanic_type')->default('other');
            $table->decimal('threshold_amount', 10, 2)->nullable();
            $table->unsignedInteger('threshold_qty')->nullable();
            $table->string('bundle_sku')->nullable();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_premium', function (Blueprint $table) {
            $table->dropColumn([
                'mechanic_type',
                'threshold_amount',
                'threshold_qty',
                'bundle_sku',
                'notes',
            ]);
        });
    }
};
