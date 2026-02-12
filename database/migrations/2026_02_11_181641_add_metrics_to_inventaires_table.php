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
        Schema::table('inventaires', function (Blueprint $table) {
            $table->decimal('prix_achat', 15, 2)->nullable()->after('quantite');
            $table->decimal('prix_vente', 15, 2)->nullable()->after('prix_achat');
            $table->decimal('remise', 15, 2)->nullable()->after('prix_vente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventaires', function (Blueprint $table) {
            $table->dropColumn(['prix_achat', 'prix_vente', 'remise']);
        });
    }
};
