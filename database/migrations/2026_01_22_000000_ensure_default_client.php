<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Client;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure a client with ID 1 exists to satisfy foreign key constraints
        // when VenteController defaults client_id to 1.
        $client = Client::find(1);
        if (!$client) {
            Client::create([
                'id' => 1,
                'nom' => 'ANONYME',
                'telephone' => '00000000',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't necessarily want to delete it on rollback as it might be in use
    }
};
