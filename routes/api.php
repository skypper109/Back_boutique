<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FactureController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\AnneeController;
use App\Http\Controllers\UserStatusController;


// Auth Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// User and Boutique Status Check Routes (must be authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/check-status', [UserStatusController::class, 'checkUserStatus']);
    Route::get('/boutique/check-status', [UserStatusController::class, 'checkBoutiqueStatus']);
});

// Protected Routes with User and Boutique Active Status Check
Route::middleware(['auth:sanctum', 'check.user.active', 'check.boutique.active'])->group(function () {
    Route::get('/user', [AuthController::class, 'index']);
    Route::get('/user/{id}', [AuthController::class, 'showUser']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::put('/user/{id}', [AuthController::class, 'updateUser']);
    Route::patch('/user/{id}/toggle-status', [AuthController::class, 'toggleUserStatus']);
    Route::delete('/user/{id}', [AuthController::class, 'deleteUser']);

    // Categories Routes
    Route::apiResource('categories', CategorieController::class);

    // Products Routes
    Route::get('produits/trashed', [ProduitController::class, 'trashed']);
    Route::post('produits/{id}/restore', [ProduitController::class, 'restore']);
    Route::get('produits/editProd/{produit}', [ProduitController::class, 'editProd']);
    Route::apiResource('produits', ProduitController::class);

    // Profile Routes
    Route::get('profil/{id}', [AuthController::class, 'getProfil']);
    Route::put('profil/{id}', [AuthController::class, 'updateProfil']);

    // Reapprovisionnement
    Route::post('reappro', [ProduitController::class, 'reapproCreate']);
    Route::get('reappro', [ProduitController::class, 'reapproIndex']);
    Route::get('rupture', [ProduitController::class, 'rupture']);
    Route::get('stock', [ProduitController::class, 'stock']);

    // Inventaires
    Route::get('inventaires', [ProduitController::class, 'inventaire']);
    Route::post('inventaires', [ProduitController::class, 'inventaireDate']);
    Route::get('inventaires/{dateDebut}/{dateFin}', [ProduitController::class, 'inventaireDate']);

    // Sales (Ventes)
    Route::apiResource('ventes', VenteController::class);
    Route::post('retourvente', [VenteController::class, 'modifierVente']);
    Route::get('annulevente/{id}', [VenteController::class, 'annuleVente']);

    // Dashboard / Stats
    Route::get('ventesparjour/{year}/{month}/{day}', [VenteController::class, 'nBventeDateJour']);
    Route::get('ventesparmois/{year}/{month}', [VenteController::class, 'nBventeDateMois']);
    Route::get('ventesparannee/{year}', [VenteController::class, 'nBventeDateAnnee']);
    Route::get('recentvente', [VenteController::class, 'recentVente']);
    Route::get('topvente', [VenteController::class, 'topVente']);
    Route::get('topvente/{limit}', [VenteController::class, 'topVenteByLimit']);
    Route::get('historique', [VenteController::class, 'historiqueVente']);
    Route::get('historique/{id}', [VenteController::class, 'historiqueVenteSelected']);
    // Years (Annees)
    Route::get('anneeVente', [AnneeController::class, 'index']);
    Route::post('anneeVente', [AnneeController::class, 'store']);
    Route::patch('anneeVente/{id}/toggle-status', [AnneeController::class, 'toggleStatus']);
    Route::delete('anneeVente/{id}', [AnneeController::class, 'destroy']);

    // Factures
    Route::get('facturations/{annee?}', [FactureController::class, 'index']);
    Route::get('facture/{id}', [FactureController::class, 'detailFacture']);

    // Clients
    Route::get('clients', [ClientController::class, 'index']);
    Route::get('clientsfidele', [ClientController::class, 'clientFidele']);
    Route::get('clients/{annee}', [ClientController::class, 'clientAnnee']);

    // Boutiques
    Route::get('summary', [ProduitController::class, 'summary']);
    Route::post('produits/import-csv', [ProduitController::class, 'importCSV']);
    Route::get('boutiques-reports', [BoutiqueController::class, 'allStats']);
    Route::get('boutiques/{id}/stats', [BoutiqueController::class, 'stats']);

    // CA & Stats
    Route::get('chiffre', [VenteController::class, 'chiffre']);
    Route::get('chiffre/{annee}', [VenteController::class, 'getVenteByAnnee']);
    Route::get('chiffre/{annee}/{mois}', [VenteController::class, 'getVenteByMois']);

    Route::apiResource('boutiques', BoutiqueController::class);
});
