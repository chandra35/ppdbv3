<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('nomor_rule_id')->constrained('nomor_rules')->cascadeOnDelete();
            $table->unsignedInteger('last_number')->default(0);
            $table->string('last_generated_value', 150)->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();

            $table->unique('nomor_rule_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_sequences');
    }
};
