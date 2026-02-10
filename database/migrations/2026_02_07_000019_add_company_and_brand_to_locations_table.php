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
        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            $table->foreignId('brand_client_id')->nullable()->after('company_id')->constrained('brand_clients')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_client_id');
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
