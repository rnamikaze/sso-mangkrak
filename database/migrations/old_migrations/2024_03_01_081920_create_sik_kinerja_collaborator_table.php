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
        Schema::create('sik_kinerja_collaborator', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('task_id');
            $table->foreign('task_id')->references('id')->on('sik_biodata')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sik_kinerja_collaborator');
    }
};
