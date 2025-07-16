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
        Schema::create('sik_jabatan_struk_dos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('fakultas_fr_id')->nullable();
            $table->foreign('fakultas_fr_id')->references('id')->on('sik_jabatan_fungsional')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->tinyInteger('level_jsd');
            $table->string('code');
            $table->string('name');
            $table->string('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sik_jabatan_struk_dos');
    }
};
