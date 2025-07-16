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
        Schema::create('sik_pengajuan_cuti', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->tinyInteger('cuti_type');
            $table->json('cuti_date_arr')->nullable();
            $table->tinyInteger('pengajuan_type');
            $table->unsignedBigInteger('id_pegawai_penugasan');
            $table->text('komentar')->nullable();
            $table->json('bukti_arr')->nullable();
            $table->tinyInteger('status_pengajuan')->default(0);
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sik_pengajuan_cuti');
    }
};
