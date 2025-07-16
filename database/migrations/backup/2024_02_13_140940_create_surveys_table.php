<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PersonData;
use App\Models\Unit;
use App\Models\LevelSurvey;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sku_surveys', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('nama_id');
            $table->foreign('nama_id')->references('id')->on('sku_person_data')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedBigInteger('kode_unit_id');
            $table->foreign('kode_unit_id')->references('id')->on('sku_units')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedBigInteger('level_survey_id');
            $table->foreign('level_survey_id')->references('id')->on('sku_level_surveys')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->text("komentar");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
