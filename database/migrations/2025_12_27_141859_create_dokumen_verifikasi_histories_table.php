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
        Schema::create('dokumen_verifikasi_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('dokumen_id');
            $table->uuid('user_id')->nullable();
            $table->string('action'); // approve, reject, revisi, cancel
            $table->string('status_from')->nullable(); // status sebelum
            $table->string('status_to'); // status sesudah
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('dokumen_id')->references('id')->on('calon_dokumen')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('dokumen_id');
            $table->index('user_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_verifikasi_histories');
    }
};
