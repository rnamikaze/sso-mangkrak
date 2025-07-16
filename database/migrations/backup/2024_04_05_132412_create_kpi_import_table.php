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
        Schema::create('kpi_import', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('start_periode');
            $table->date('end_periode');
            $table->text('filename');
            $table->json('raw_data');
            $table->tinyInteger('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_import');
    }
};
