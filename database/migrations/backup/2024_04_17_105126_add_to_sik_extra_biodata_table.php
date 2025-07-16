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
        $defJson = ["hadir" => 0, "terlambat" => 0, "tidak_hadir" => 0];
        Schema::table('sik_extra_biodata', function (Blueprint $table) {
            //
            $table->json('current_absensi_json')->default(json_encode(["hadir" => 0, "terlambat" => 0, "tidak_hadir" => 0]));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sik_extra_biodata', function (Blueprint $table) {
            //
        });
    }
};
