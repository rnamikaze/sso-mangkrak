<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Unit;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sku_person_data', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('nik');
            $table->string('nama');
            $table->unsignedBigInteger('kode_unit_id');
            $table->foreign('kode_unit_id')->references('id')->on('sku_units')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->string('jabatan');
            $table->string('gelar_depan')->default('gelar kosong');
            $table->string('gelar_belakang')->default('gelar kosong');
            $table->tinyInteger('kelamin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_data');
    }
};
