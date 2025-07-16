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
            $table->integer('prodi_id')->after('img_storage')->nullable();
            $table->integer('fakultas_id')->after('img_storage')->nullable();
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
