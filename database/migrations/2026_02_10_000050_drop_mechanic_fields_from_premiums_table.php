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
        Schema::table('premiums', function (Blueprint $table) {
            $table->dropColumn([
                'default_mechanic_type',
                'default_threshold_amount',
                'default_threshold_qty',
                'default_bundle_sku',
                'default_notes',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('premiums', function (Blueprint $table) {
            $table->string('default_mechanic_type')->default('other');
            $table->decimal('default_threshold_amount', 10, 2)->nullable();
            $table->unsignedInteger('default_threshold_qty')->nullable();
            $table->string('default_bundle_sku')->nullable();
            $table->text('default_notes')->nullable();
        });
    }
};
