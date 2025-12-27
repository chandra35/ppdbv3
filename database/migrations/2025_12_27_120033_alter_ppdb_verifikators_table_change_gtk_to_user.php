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
        Schema::table('ppdb_verifikators', function (Blueprint $table) {
            // Drop old unique constraint first
            $table->dropUnique(['gtk_id', 'ppdb_settings_id']);
            
            // Drop old column
            $table->dropColumn('gtk_id');
            
            // Add new user_id column
            $table->uuid('user_id')->after('id');
            
            // Add new unique constraint
            $table->unique(['user_id', 'ppdb_settings_id']);
            
            // Add index for user_id
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppdb_verifikators', function (Blueprint $table) {
            // Drop new constraint
            $table->dropUnique(['user_id', 'ppdb_settings_id']);
            $table->dropIndex(['user_id']);
            
            // Drop user_id
            $table->dropColumn('user_id');
            
            // Add back gtk_id
            $table->uuid('gtk_id')->after('id');
            
            // Restore old unique constraint
            $table->unique(['gtk_id', 'ppdb_settings_id']);
        });
    }
};
