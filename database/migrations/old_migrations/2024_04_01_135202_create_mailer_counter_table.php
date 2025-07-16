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
        Schema::create('mailer_counter', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger("daily_counter")->default(0);
            $table->tinyInteger("active");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailer_counter');
    }
};
