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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price_monthly')->default(0);
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedInteger('max_locations')->nullable();
            $table->unsignedInteger('max_dashboards')->nullable();
            $table->unsignedInteger('max_customers')->nullable();
            $table->string('inventory_level')->default('basic');
            $table->json('features')->nullable();
            $table->timestamps();
        });

        DB::table('plans')->insert([
            [
                'name' => 'Essential',
                'price_monthly' => 450,
                'max_products' => 10,
                'max_locations' => 3,
                'max_dashboards' => 1,
                'max_customers' => 5,
                'inventory_level' => 'basic',
                'features' => json_encode([
                    'engagement_tracking' => true,
                    'alerts_notifications' => true,
                    'multi_language' => true,
                    'export_reports' => true,
                    'event_management' => true,
                    'real_time_kpi' => true,
                    'photo_checkin' => true,
                    'sampling_tracking' => true,
                    'event_calendar' => true,
                    'historical_analysis' => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Advanced',
                'price_monthly' => 980,
                'max_products' => 30,
                'max_locations' => null,
                'max_dashboards' => 2,
                'max_customers' => 20,
                'inventory_level' => 'full',
                'features' => json_encode([
                    'role_based_dashboards' => true,
                    'inventory_reporting' => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ultimate',
                'price_monthly' => 1750,
                'max_products' => null,
                'max_locations' => null,
                'max_dashboards' => 4,
                'max_customers' => null,
                'inventory_level' => 'full',
                'features' => json_encode([
                    'multi_stakeholder_dashboards' => true,
                    'enterprise_reporting' => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
