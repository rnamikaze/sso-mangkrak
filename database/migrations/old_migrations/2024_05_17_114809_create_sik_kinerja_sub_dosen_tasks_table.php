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
        Schema::create('sik_kinerja_sub_dosen_tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->date('periode_start')->nullable();

            $table->unsignedBigInteger('task_id');
            $table->foreign('task_id')->references('id')->on('sik_kinerja_dosen_tasks')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->tinyInteger('assigner_level')->nullable();

            $table->date('due_date')->nullable();
            $table->date('realize_date')->nullable();
            $table->string('title');
            $table->json('collab_list_biodata_id');

            $table->tinyInteger('progress_int');

            $table->text('comment');
            $table->tinyInteger('active')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sik_kinerja_sub_dosen_tasks');
    }
};
