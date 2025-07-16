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
        Schema::create('jabatan_base_nominal_models', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('base_nominal_kpi')->default(0);
            $table->tinyInteger('jabatan_level');
            $table->string('name_alias')->nullable();
            $table->tinyInteger('active')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jabatan_base_nominal_models');
    }
};
