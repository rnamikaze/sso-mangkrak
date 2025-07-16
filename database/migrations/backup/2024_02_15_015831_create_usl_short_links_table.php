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
        Schema::create('usl_short_links', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->tinyInteger('domain_id');
            $table->string('prefix')->nullable();
            $table->string('url')->unique();
            $table->string('title');
            $table->integer('visitor_count');
            $table->text('destination_url');
            $table->tinyInteger('active');
            $table->unsignedBigInteger('owned_by')->default(1);
            $table->foreign('owned_by')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usl_short_links');
    }
};
