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
        // First map existing data if any
        DB::table('users')->where('role', 'admin0')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'admin1')->update(['role' => 'gestionnaire']);
        DB::table('users')->where('role', 'admin2')->update(['role' => 'vendeur']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'gestionnaire', 'vendeur', 'comptable'])->default('admin')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin0', 'admin1', 'admin2'])->default('admin0')->change();
        });

        // Map back
        DB::table('users')->where('role', 'admin')->update(['role' => 'admin0']);
        DB::table('users')->where('role', 'gestionnaire')->update(['role' => 'admin1']);
        DB::table('users')->where('role', 'vendeur')->update(['role' => 'admin2']);
        DB::table('users')->where('role', 'comptable')->update(['role' => 'admin2']); // Fallback
    }
};
