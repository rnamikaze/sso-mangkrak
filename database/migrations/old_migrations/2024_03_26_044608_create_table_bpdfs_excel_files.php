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
        Schema::create('table_bpdfs_excel_files', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('filename');
            $table->integer('view_count')->nullable();
            $table->tinyInteger('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_bpdfs_excel_files');
    }
};
