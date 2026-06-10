<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('registrasis', function (Blueprint $table) {
            $table->string('jurusan_awal')->nullable()->after('jurusan_excel');
            $table->boolean('pindah_jurusan')->default(false)->after('jurusan_final');
        });
    }

    public function down(): void
    {
        Schema::table('registrasis', function (Blueprint $table) {
            $table->dropColumn(['jurusan_awal', 'pindah_jurusan']);
        });
    }
};
