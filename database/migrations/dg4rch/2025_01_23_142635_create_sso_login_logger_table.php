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
        Schema::create('sso_login_logger', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('master_id');
            $table->string('action')->nullable();
            $table->string('ip_log')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('formated_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sso_login_logger');
    }
};
