<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $boutique_id = $this->getBoutiqueId();
        $client = DB::table('clients as c')
            ->join('factures as f', 'f.client_id', 'c.id')
            ->join('facture_ventes as fv', 'fv.facture_id', 'f.id')
            ->join('ventes as v', 'v.id', 'fv.vente_id')
            ->join('detail_ventes as dv', 'dv.vente_id', 'v.id')
            ->select(
                'c.nom as nomClient',
                'c.id as idClient',
                'f.id as idFacture',
                'c.telephone as telClient',
                'f.statut as statut',
                'v.date_vente as dateVente',
                'v.montant_total as montant',
                DB::raw('SUM(dv.quantite) as quantite')
            )
            ->where('v.boutique_id', $boutique_id)
            ->whereRaw('c.nom <> ?', 'Particulier')
            ->groupBy('c.id')
            ->orderBy('c.id', 'desc')
            ->get();
        return response()->json($client, 200);
    }

    public function clientAnnee($annee) {}

    public function clientFidele()
    {
        $boutique_id = $this->getBoutiqueId();
        $client = DB::table('clients as c')
            ->join('factures as f', 'f.client_id', 'c.id')
            ->join('facture_ventes as fv', 'fv.facture_id', 'f.id')
            ->join('ventes as v', 'v.id', 'fv.vente_id')
            ->join('detail_ventes as dv', 'dv.vente_id', 'v.id')
            ->select(
                'c.nom as nomClient',
                'c.id as idClient',
                'f.id as idFacture',
                'c.telephone as telClient',
                'f.statut as statut',
                'v.date_vente as dateVente',
                'v.montant_total as montant',
                DB::raw('SUM(dv.quantite) as quantite')
            )
            ->where('v.boutique_id', $boutique_id)
            ->whereRaw('c.nom <> ?', 'Particulier')
            ->groupBy('c.id')
            ->orderBy('quantite', 'desc')
            ->limit(5)
            ->get();
        return response()->json($client, 200);
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
    public function show(Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        //
    }
}
