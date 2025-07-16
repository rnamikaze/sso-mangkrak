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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('master_id');
            $table->foreign('master_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->string('google_id');
            $table->string('google_email');
            $table->text('google_avatar_url');
            $table->json('google_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
