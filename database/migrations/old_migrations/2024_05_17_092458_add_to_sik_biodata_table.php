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
            $table->unsignedBigInteger('jabatan_strukdos_id')->after('status_serdos')->nullable();
            $table->foreign('jabatan_strukdos_id')->references('id')->on('sik_jabatan_struk_dos')->onUpdate('CASCADE')->onDelete('SET NULL');
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
