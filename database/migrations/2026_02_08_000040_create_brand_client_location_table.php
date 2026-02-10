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
        Schema::create('brand_client_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_client_id')->constrained('brand_clients')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['brand_client_id', 'location_id']);
        });

        $now = now();
        $rows = DB::table('locations')
            ->whereNotNull('brand_client_id')
            ->select('id', 'brand_client_id')
            ->get()
            ->map(function ($location) use ($now) {
                return [
                    'brand_client_id' => $location->brand_client_id,
                    'location_id' => $location->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();

        if (!empty($rows)) {
            DB::table('brand_client_location')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_client_location');
    }
};
