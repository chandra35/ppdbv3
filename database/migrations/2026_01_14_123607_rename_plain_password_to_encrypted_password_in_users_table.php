<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename column and increase size for encrypted data
            $table->renameColumn('plain_password', 'encrypted_password');
        });

        // Increase column size for encrypted data (encrypted strings are longer)
        Schema::table('users', function (Blueprint $table) {
            $table->string('encrypted_password', 255)->nullable()->change();
        });

        // Encrypt existing plain passwords
        $users = DB::table('users')->whereNotNull('encrypted_password')->get();
        foreach ($users as $user) {
            // Only encrypt if not already encrypted (plain passwords are short)
            if (strlen($user->encrypted_password) < 50) {
                try {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'encrypted_password' => Crypt::encryptString($user->encrypted_password)
                        ]);
                } catch (\Exception $e) {
                    // Skip if encryption fails
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Decrypt passwords back to plain
        $users = DB::table('users')->whereNotNull('encrypted_password')->get();
        foreach ($users as $user) {
            try {
                $decrypted = Crypt::decryptString($user->encrypted_password);
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['encrypted_password' => $decrypted]);
            } catch (\Exception $e) {
                // Skip if decryption fails
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('encrypted_password', 50)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('encrypted_password', 'plain_password');
        });
    }
};
