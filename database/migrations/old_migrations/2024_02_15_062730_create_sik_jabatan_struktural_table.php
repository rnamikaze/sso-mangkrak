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
        Schema::create('sik_jabatan_struktural', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('js_code');
            $table->string('js_name');
            $table->tinyInteger('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sik_jabatan_struktural');
    }
};
