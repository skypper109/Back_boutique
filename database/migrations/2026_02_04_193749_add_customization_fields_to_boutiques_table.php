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
        Schema::table('boutiques', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('email');
            $table->text('description_facture')->nullable();
            $table->text('description_bordereau')->nullable();
            $table->text('description_recu')->nullable();
            $table->text('footer_facture')->nullable();
            $table->text('footer_bordereau')->nullable();
            $table->text('footer_recu')->nullable();
            $table->string('couleur_principale')->default('#4f46e5');
            $table->string('couleur_secondaire')->default('#10b981');
            $table->string('devise')->default('FCFA');
            $table->string('format_facture')->default('A4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boutiques', function (Blueprint $table) {
            $table->dropColumn([
                'logo',
                'description_facture',
                'description_bordereau',
                'description_recu',
                'footer_facture',
                'footer_bordereau',
                'footer_recu',
                'couleur_principale',
                'couleur_secondaire',
                'devise',
                'format_facture'
            ]);
        });
    }
};
