<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $annee = null)
    {
        $user = Auth::user();
        $boutique_id = $user->boutique_id;

        $query = DB::table('factures as f')
            ->join('clients as c', 'c.id', '=', 'f.client_id')
            ->join('facture_ventes as fv', 'fv.facture_id', '=', 'f.id')
            ->join('ventes as v', 'v.id', '=', 'fv.vente_id')
            ->where('v.boutique_id', $boutique_id)
            ->select(
                'f.id as idFacture',
                'c.nom as nomClient',
                'c.telephone as telClient',
                'f.montant_total as montant',
                'f.statut',
                'f.date_facturation as dateVente',
                'f.created_at'
            );

        $selectedYear = $annee ?: $request->annee;
        if ($selectedYear) {
            $query->whereYear('f.date_facturation', $selectedYear);
        }

        $factures = $query->orderBy('f.date_facturation', 'desc')->get();

        return response()->json($factures, 200);
    }

    public function detailFacture($IDfacture)
    {
        // Utilisation d'Eloquent pour plus de robustesse
        $facture = Facture::with(['client', 'factureVentes.vente.detailVentes.produit'])
            ->where('id', $IDfacture)
            ->firstOrFail();

        $produitAchat = [];
        foreach ($facture->factureVentes as $fv) {
            if ($fv->vente) {
                foreach ($fv->vente->detailVentes as $dv) {
                    $produitAchat[] = [
                        'nomProduit' => $dv->produit ? $dv->produit->nom : 'Produit inconnu',
                        'quantite' => $dv->quantite,
                        'prixUnitaire' => $dv->prix_unitaire,
                        'montant' => $dv->montant
                    ];
                }
            }
        }

        $response = [
            'nomClient' => $facture->client->nom,
            'numeroClient' => $facture->client->telephone,
            'adresseClient' => $facture->client->adresse ?? 'N/A',
            'dateFacture' => $facture->date_facturation,
            'montant_total' => $facture->montant_total,
            'produitAchat' => $produitAchat
        ];

        return response()->json([$response]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Facture $facture)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Facture $facture)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Facture $facture)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facture $facture)
    {
        //
    }
}
