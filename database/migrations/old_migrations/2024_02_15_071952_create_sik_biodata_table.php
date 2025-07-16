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
        Schema::create('sik_biodata', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('nik', 18)->unique();
            $table->string('fullname')->nullable();
            $table->tinyInteger('kelamin');
            $table->integer('tinggi_badan');
            $table->integer('berat_badan');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->text('alamat_rumah')->nullable();
            $table->string('telepon', 16);
            $table->string('email')->nullable();
            $table->string('pendidikan_terakhir');
            $table->string('no_bpjs_kes', 18);
            $table->string('no_bpjs_kerja', 18);
            $table->text('alamat')->nullable();
            $table->string('kerabat_nama');
            $table->string('kerabat_hubungan');
            $table->string('kerabat_telepon', 16);
            $table->string('status', 30);
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('sik_unit_kerja')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedBigInteger('jabatan_struktural_id');
            $table->foreign('jabatan_struktural_id')->references('id')->on('sik_jabatan_struktural')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedBigInteger('jabatan_fungsional_id');
            $table->foreign('jabatan_fungsional_id')->references('id')->on('sik_jabatan_fungsional')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->tinyInteger('status_serdos');
            $table->tinyInteger('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sik_biodata');
    }
};
