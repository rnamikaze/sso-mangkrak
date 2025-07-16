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
        Schema::create('user_online_monitor', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('master_id');
            $table->string('name');
            $table->boolean('is_online')->default(false);
            $table->dateTime('last_seen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_online_monitor');
    }
};
