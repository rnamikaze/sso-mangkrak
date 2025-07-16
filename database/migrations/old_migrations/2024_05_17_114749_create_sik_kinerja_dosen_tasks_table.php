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
        Schema::create('sik_kinerja_dosen_tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->date('periode_start')->nullable();
            $table->unsignedBigInteger('assigned_biodata_id');
            $table->foreign('assigned_biodata_id')->references('id')->on('sik_biodata')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->tinyInteger('assigner_level')->nullable();
            $table->json('sub_task_list_id')->nullable();

            $table->date('due_date')->nullable();
            $table->date('realize_date')->nullable();

            $table->string('title');
            $table->unsignedTinyInteger('progress_percentage')->default(0);

            $table->text('comment');
            $table->tinyInteger('active')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sik_kinerja_dosen_tasks');
    }
};
