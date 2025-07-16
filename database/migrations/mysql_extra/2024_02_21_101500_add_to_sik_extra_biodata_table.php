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
        Schema::table('sik_extra_biodata', function (Blueprint $table) {
            //
            // 0: Belum Menikah, 1: Sudah Menikah, 2: Cerai
            $table->tinyInteger('status_pernikahan')->after('biodata_id')->nullable();
            $table->string('kompetensi')->after('url_ijazah')->nullable();
            $table->string('bakat')->after('url_ijazah')->nullable();
            $table->string('minat')->after('url_ijazah')->nullable();
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
