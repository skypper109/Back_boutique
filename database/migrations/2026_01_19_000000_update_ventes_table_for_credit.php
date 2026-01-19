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
        Schema::table('ventes', function (Blueprint $table) {
            $table->enum('type_paiement', ['contant', 'credit','proforma'])->default('contant')->after('montant_total');
            $table->decimal('montant_avance', 15, 2)->default(0)->after('type_paiement');
            $table->decimal('montant_restant', 15, 2)->default(0)->after('montant_avance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropColumn(['type_paiement', 'montant_avance', 'montant_restant']);
        });
    }
};
