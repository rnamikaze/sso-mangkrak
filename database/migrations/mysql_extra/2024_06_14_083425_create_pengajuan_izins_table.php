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
        Schema::create('pengajuan_izins', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('id_pengaju');
            $table->tinyInteger('cuti_type');
            $table->json('cuti_date_arr')->nullable();
            // $table->tinyInteger('pengajuan_type');
            $table->unsignedBigInteger('id_pegawai_penugasan');
            $table->text('komentar')->nullable();
            $table->json('file_pendukung_arr')->nullable();
            $table->tinyInteger('status_pengajuan')->default(0);
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izins');
    }
};
