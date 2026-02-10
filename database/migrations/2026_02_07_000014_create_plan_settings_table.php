<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plan_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('active_plan_id')->constrained('plans')->cascadeOnDelete();
            $table->timestamps();
        });

        $defaultPlanId = DB::table('plans')->where('name', 'Essential')->value('id');
        if ($defaultPlanId) {
            DB::table('plan_settings')->insert([
                'active_plan_id' => $defaultPlanId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_settings');
    }
};
