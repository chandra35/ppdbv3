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
            $table->date('tanggal_dibuka')->nullable()->after('persyaratan');
            $table->date('tanggal_ditutup')->nullable()->after('tanggal_dibuka');
            $table->string('status', 20)->default('draft')->after('is_active');
            // status: draft, active, closed, finished
            
            // Nomor registrasi
            $table->string('prefix_nomor', 20)->nullable()->after('icon');
            $table->unsignedInteger('counter_nomor')->default(0)->after('prefix_nomor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            $table->dropColumn(['tanggal_dibuka', 'tanggal_ditutup', 'status', 'prefix_nomor', 'counter_nomor']);
        });
    }
};
