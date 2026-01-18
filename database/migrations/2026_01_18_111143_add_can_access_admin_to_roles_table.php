<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('can_access_admin')->default(false)->after('is_system');
        });

        // Set default values for existing roles
        // Roles yang bisa akses admin panel
        DB::table('roles')
            ->whereIn('name', ['admin', 'super-admin', 'operator', 'verifikator', 'mas-admin', 'content-manager', 'penguji'])
            ->update(['can_access_admin' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('can_access_admin');
        });
    }
};
