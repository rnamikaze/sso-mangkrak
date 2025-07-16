<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PersonData;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sku_level_surveys', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->tinyInteger("level_survey");
            $table->string("nama_level_survey");
            $table->tinyInteger("active");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_surveys');
    }
};
