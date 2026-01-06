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
        // Fix for SQLite dumping foreign keys
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('ventes_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('boutique_id')->nullable()->constrained()->onDelete('cascade');
                $table->decimal('montant_total', 10, 2);
                $table->date('date_vente');
                $table->timestamps();
            });

            DB::statement('INSERT INTO ventes_temp (id, client_id, user_id, boutique_id, montant_total, date_vente, created_at, updated_at) 
                           SELECT id, client_id, user_id, boutique_id, montant_total, date_vente, created_at, updated_at FROM ventes');

            Schema::drop('ventes');
            Schema::rename('ventes_temp', 'ventes');
        } else {
            Schema::table('ventes', function (Blueprint $table) {
                if (Schema::hasColumn('ventes', 'magasin_id')) {
                    $table->dropForeign(['magasin_id']);
                    $table->dropColumn('magasin_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->foreignId('magasin_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
