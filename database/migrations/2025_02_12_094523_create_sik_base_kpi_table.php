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
        Schema::create('sik_base_kpi', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('base')->default(200000);
            $table->unsignedTinyInteger('status_kerja_id');
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sik_base_kpi');
    }
};
