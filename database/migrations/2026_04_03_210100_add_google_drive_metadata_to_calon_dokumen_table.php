<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calon_dokumen', function (Blueprint $table) {
            $table->string('remote_file_id', 255)->nullable()->after('file_path');
            $table->string('remote_file_url', 500)->nullable()->after('remote_file_id');
        });
    }

    public function down(): void
    {
        Schema::table('calon_dokumen', function (Blueprint $table) {
            $table->dropColumn([
                'remote_file_id',
                'remote_file_url',
            ]);
        });
    }
};
