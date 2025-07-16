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
        Schema::create('kabupaten_indonesia', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedInteger('provinsi_id');
            $table->foreign('provinsi_id')->references('name_id')->on('provinsi_indonesia')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->string('name');
            $table->string('alt_name');
            $table->unsignedInteger('name_id')->unique();
            $table->string('lat', 20);
            $table->string('lon', 20);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kabupaten_indonesia');
    }
};
