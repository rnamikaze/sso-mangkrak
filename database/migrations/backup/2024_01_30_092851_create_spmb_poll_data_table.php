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
        Schema::create('spmb_poll_data', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('poll')->default('puas');
            $table->tinyInteger('poll_code')->default(2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_poll_data');
    }
};
