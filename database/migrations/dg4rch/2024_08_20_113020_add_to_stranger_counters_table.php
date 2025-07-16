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
        Schema::table('sso_stranger_counters', function (Blueprint $table) {
            //
            $table->string('ip_address', 30);
            $table->text('user_agent');
            $table->text('browser');
            $table->text('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stranger_counters', function (Blueprint $table) {
            //
        });
    }
};
