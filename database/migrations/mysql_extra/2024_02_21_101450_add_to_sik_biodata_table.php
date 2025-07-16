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
        Schema::table('sik_biodata', function (Blueprint $table) {
            //
            $table->string('nik_ktp', 20)->after('nik')->nullable();
            $table->date('awal_kerja')->after('tanggal_lahir')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sik_biodata', function (Blueprint $table) {
            //
        });
    }
};
