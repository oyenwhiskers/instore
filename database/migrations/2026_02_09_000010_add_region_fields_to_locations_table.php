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
            $table->string('country', 100)->nullable()->after('name');
            $table->string('state', 100)->nullable()->after('country');
            $table->string('district', 100)->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['country', 'state', 'district']);
        });
    }
};
