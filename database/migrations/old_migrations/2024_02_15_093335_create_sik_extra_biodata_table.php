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
        Schema::create('sik_extra_biodata', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('biodata_id');
            $table->foreign('biodata_id')->references('id')->on('sik_biodata')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->string('url_ijazah');
            $table->string('provinsi');
            $table->string('kota_kab');
            $table->string('kecamatan');
            $table->string('desa_kel');
            $table->unsignedInteger('rt');
            $table->unsignedInteger('rw');
            $table->string('kode_pos', 10);

            $table->string('provinsi_2')->nullable();
            $table->string('kota_kab_2')->nullable();
            $table->string('kecamatan_2')->nullable();
            $table->string('desa_kel_2')->nullable();
            $table->unsignedInteger('rt_2')->nullable();
            $table->unsignedInteger('rw_2')->nullable();
            $table->string('kode_pos_2', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sik_extra_biodata');
    }
};
