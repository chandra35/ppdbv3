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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('calon_siswa_id')->nullable();
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('subject');
            $table->string('type')->default('general'); // nomor_tes, revisi, diterima, ditolak, etc
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->text('message_preview')->nullable(); // Preview singkat isi email
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('calon_siswa_id')
                ->references('id')
                ->on('calon_siswas')
                ->onDelete('set null');
            
            $table->index(['status', 'created_at']);
            $table->index('type');
            $table->index('to_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
