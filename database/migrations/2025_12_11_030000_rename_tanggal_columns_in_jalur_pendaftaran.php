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
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            // Rename columns to match model
            $table->renameColumn('tanggal_dibuka', 'tanggal_buka');
            $table->renameColumn('tanggal_ditutup', 'tanggal_tutup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            $table->renameColumn('tanggal_buka', 'tanggal_dibuka');
            $table->renameColumn('tanggal_tutup', 'tanggal_ditutup');
        });
    }
};
