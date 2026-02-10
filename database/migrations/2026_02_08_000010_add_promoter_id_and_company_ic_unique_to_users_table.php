<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('promoter_id')->nullable()->unique()->after('id');
            $table->dropUnique(['ic_number']);
            $table->unique(['company_id', 'ic_number']);
        });

        $promoters = DB::table('users')
            ->where('role', 'promoter')
            ->whereNull('promoter_id')
            ->select('id')
            ->get();

        foreach ($promoters as $promoter) {
            do {
                $promoterId = 'PRM-' . Str::upper(Str::random(6));
            } while (DB::table('users')->where('promoter_id', $promoterId)->exists());

            DB::table('users')
                ->where('id', $promoter->id)
                ->update(['promoter_id' => $promoterId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'ic_number']);
            $table->dropUnique(['promoter_id']);
            $table->dropColumn('promoter_id');
            $table->unique('ic_number');
        });
    }
};
