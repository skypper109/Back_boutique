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
        // 1. Add boutique_id to factures
        if (!Schema::hasColumn('factures', 'boutique_id')) {
            Schema::table('factures', function (Blueprint $table) {
                $table->foreignId('boutique_id')->nullable()->after('client_id')->constrained('boutiques')->onDelete('cascade');
            });
        }

        // 2. Add user_id to boutiques to track the creator
        if (!Schema::hasColumn('boutiques', 'user_id')) {
            Schema::table('boutiques', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }

        // 3. Data Sync: Link existing factures to boutiques via their associated sales
        DB::statement("
            UPDATE factures
            SET boutique_id = (
                SELECT v.boutique_id
                FROM ventes v
                JOIN facture_ventes fv ON fv.vente_id = v.id
                WHERE fv.facture_id = factures.id
                LIMIT 1
            )
            WHERE boutique_id IS NULL
        ");

        // 4. Fallback for orphans: Link to the first boutique if still null
        $firstBoutique = DB::table('boutiques')->first();
        if ($firstBoutique) {
            DB::table('factures')->whereNull('boutique_id')->update(['boutique_id' => $firstBoutique->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropForeign(['boutique_id']);
            $table->dropColumn('boutique_id');
        });

        Schema::table('boutiques', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
