<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_rule', 100);
            $table->enum('jenis_nomor', ['registrasi', 'tes'])->index();
            $table->enum('scope_type', ['global', 'tahun', 'jalur', 'gelombang'])->default('global')->index();
            $table->uuid('scope_id')->nullable()->index();
            $table->string('prefix', 30)->nullable();
            $table->string('format', 150)->default('{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}');
            $table->unsignedTinyInteger('digit')->default(4);
            $table->unsignedInteger('nomor_awal')->default(1);
            $table->unsignedInteger('nomor_akhir')->nullable();
            $table->enum('mode_counter', ['reset', 'manual', 'lanjut_rule_lain'])->default('reset');
            $table->uuid('source_rule_id')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('source_rule_id')
                ->references('id')
                ->on('nomor_rules')
                ->nullOnDelete();

            $table->unique(['jenis_nomor', 'scope_type', 'scope_id'], 'nomor_rules_unique_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_rules');
    }
};
