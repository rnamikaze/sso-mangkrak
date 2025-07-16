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
        Schema::create('app_insts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('appname');
            $table->string('appid')->unique();
            $table->string('appdesc')->nullable();
            $table->text('appinfo')->nullable();
            $table->tinyInteger('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_insts');
    }
};
