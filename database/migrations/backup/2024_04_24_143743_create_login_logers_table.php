<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('master_id');
            $table->string('action')->nullable();
            $table->string('ip_log')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('formated_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logers');
    }
};
