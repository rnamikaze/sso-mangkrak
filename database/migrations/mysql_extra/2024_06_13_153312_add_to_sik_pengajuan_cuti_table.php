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
        Schema::table('sik_pengajuan_cuti', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('id_pengaju')->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sik_pengajuan_cuti', function (Blueprint $table) {
            //
        });
    }
};
