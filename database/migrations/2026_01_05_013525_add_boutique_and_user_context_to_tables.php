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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('boutique_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('ventes', function (Blueprint $table) {
            $table->foreignId('boutique_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->foreignId('boutique_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('inventaires', function (Blueprint $table) {
            $table->foreignId('boutique_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['boutique_id']);
            $table->dropColumn('boutique_id');
        });

        Schema::table('ventes', function (Blueprint $table) {
            $table->dropForeign(['boutique_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['boutique_id', 'user_id']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['boutique_id']);
            $table->dropColumn('boutique_id');
        });

        Schema::table('inventaires', function (Blueprint $table) {
            $table->dropForeign(['boutique_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['boutique_id', 'user_id']);
        });
    }
};
