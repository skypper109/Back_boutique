<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Vente;
use App\Models\Client;
use App\Models\Facture;
use App\Models\Produit;
use App\Models\Inventaire;
use App\Models\DetailVente;
use App\Models\FactureVente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;

class VenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $boutique_id = $this->getBoutiqueId();
        $ventes = Vente::with(['user', 'detailVentes.produit'])
            ->where('boutique_id', $boutique_id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($ventes, 200);
    }

    // Pour la topVente:
    public function topVente()
    {
        $boutique_id = $this->getBoutiqueId();
        $topVente = DetailVente::with('produit')
            ->whereHas('vente', function ($q) use ($boutique_id) {
                $q->where('boutique_id', $boutique_id)
                    ->where('statut', 'validee');
            })
            ->selectRaw('produit_id, prix_unitaire, SUM(quantite) as total_quantite, SUM(montant_total) as total_montant')
            ->groupBy('produit_id', 'prix_unitaire')
            ->orderByDesc('total_quantite')
            ->limit(5)
            ->get();
        return response()->json($topVente, 200);
    }

    public function topVenteByLimit($limit)
    {
        $boutique_id = $this->getBoutiqueId();
        $topVente = DetailVente::with('produit')
            ->whereHas('vente', function ($q) use ($boutique_id) {
                $q->where('boutique_id', $boutique_id)
                    ->where('statut', 'validee');
            })
            ->selectRaw('produit_id, prix_unitaire, SUM(quantite) as total_quantite, SUM(montant_total) as total_montant')
            ->groupBy('produit_id', 'prix_unitaire')
            ->orderByDesc('total_quantite')
            ->limit($limit)
            ->get();
        return response()->json($topVente, 200);
    }

    // Pour la modification des ventes effectuer en meme temps:
    public function modifierVente(Request $request)
    {
        $request->validate([
            'vente_id' => 'required|integer',
            'produits' => 'required|array',
        ]);

        $venteId = $request->vente_id;
        $vente = Vente::findOrFail($venteId);
        $user = Auth::user();
        $boutique_id = $user->boutique_id;

        $nouveauMontantTotal = 0;

        foreach ($request->produits as $produitData) {
            $produitId = $produitData['produit']['id'];
            $nouvelleQte = $produitData['quantite'];

            $detailVente = DetailVente::where('vente_id', $venteId)
                ->where('produit_id', $produitId)
                ->first();

            if (!$detailVente) continue;

            $ancienneQte = $detailVente->quantite;
            $difference = $ancienneQte - $nouvelleQte;

            if ($difference != 0) {
                // Mettre à jour le stock
                $stock = Stock::where('produit_id', $produitId)
                    ->where('boutique_id', $boutique_id)
                    ->first();

                if ($stock) {
                    $stock->quantite += $difference;
                    $stock->save();
                }

                // Enregistrer dans l'inventaire
                Inventaire::create([
                    'produit_id' => $produitId,
                    'boutique_id' => $boutique_id,
                    'user_id' => $user->id,
                    'quantite' => abs($difference),
                    'type' => $difference > 0 ? 'ajout' : 'retrait',
                    'description' => ($difference > 0 ? 'Retour Client (Partiel)' : 'Ajustement Quantité') . ' sur Vente #' . $venteId,
                    'date' => now()->format('Y-m-d')
                ]);

                if ($nouvelleQte <= 0) {
                    $detailVente->delete();
                } else {
                    $detailVente->quantite = $nouvelleQte;
                    $detailVente->montant = $detailVente->prix_unitaire * $nouvelleQte;
                    $detailVente->montant_total = $detailVente->montant; // Simplifié pour cet exemple
                    $detailVente->montant_paye = $detailVente->montant;
                    $detailVente->save();
                    $nouveauMontantTotal += $detailVente->montant;
                }
            } else {
                $nouveauMontantTotal += $detailVente->montant;
            }
        }

        // Si tous les articles ont été supprimés, on annule la vente
        if ($nouveauMontantTotal <= 0) {
            $vente->statut = 'annulee';
            $vente->save();
            return response()->json(['message' => 'Vente annulée car tous les produits ont été retournés'], 200);
        }

        $vente->montant_total = $nouveauMontantTotal;
        $vente->save();

        return response()->json(['message' => 'Vente modifiée avec succès', 'nouveau_montant' => $nouveauMontantTotal], 200);
    }

    // Pour la suppression d'une vente:
    public function supprimerVente($id)
    {
        $boutique_id = $this->getBoutiqueId();
        $vente = Vente::with('detailVentes')->where('id', $id)
            ->where('boutique_id', $boutique_id)
            ->firstOrFail();

        // Si la vente est déjà annulée, on ne peut plus rien faire
        if ($vente->statut === 'annulee') {
            return response()->json(['message' => 'Cette vente est déjà annulée'], 400);
        }

        // Remettre les produits en stock
        foreach ($vente->detailVentes as $detail) {
            $stock = Stock::where('produit_id', $detail->produit_id)
                ->where('boutique_id', $boutique_id)
                ->first();
            if ($stock) {
                $stock->quantite += $detail->quantite;
                $stock->save();
            }

            // Inventaire
            Inventaire::create([
                'produit_id' => $detail->produit_id,
                'boutique_id' => $boutique_id,
                'user_id' => Auth::id(),
                'quantite' => $detail->quantite,
                'type' => 'ajout',
                'description' => 'Annulation de Vente #' . $vente->id,
                'date' => now()->format('Y-m-d')
            ]);
        }

        $vente->statut = 'annulee';
        $vente->save();

        return response()->json(['message' => 'Vente annulée avec succès'], 200);
    }

    // Pour la suppression d'un seul element des la ventes dans la table detailVente:
    public function annuleVente($id)
    {
        $user = Auth::user();
        $boutique_id = $this->getBoutiqueId();

        $detailVente = DetailVente::with('vente')->where('id', $id)->firstOrFail();

        // Vérifier si la vente appartient à la boutique de l'utilisateur
        if ($detailVente->vente->boutique_id != $boutique_id) {
            return response()->json(["message" => "Action non autorisée"], 403);
        }

        $qte = $detailVente->quantite;
        $mtt = $detailVente->montant;
        $idProduit = $detailVente->produit_id;

        // Récupération de la vente pour la modification du montant
        $vente = $detailVente->vente;
        $vente->montant_total -= $mtt;
        $vente->save();

        // Pour la modification dans stock :
        $stock = Stock::where('produit_id', $idProduit)
            ->where('boutique_id', $boutique_id)
            ->first();

        if ($stock) {
            $stock->quantite += $qte;
            $stock->save();
        }

        // Pour l'inventaire :
        $produit = Produit::where('id', $idProduit)->first();

        // Mise à jour de la table Inventaire :
        Inventaire::create([
            'produit_id' => $idProduit,
            'boutique_id' => $boutique_id,
            'user_id' => $user->id,
            'quantite' => $qte,
            'type' => 'ajout',
            'description' => 'Annulation de Vente du ' . ($produit ? $produit->nom : 'Produit inconnu'),
            'date' => now()->format('Y-m-d')
        ]);

        $detailVente->delete();

        return response()->json(["message" => "Vente Annulée avec succès"], 200);
    }
    public function historiqueVente()
    {
        $boutique_id = $this->getBoutiqueId();
        $ventes = Vente::with([
            'detailVentes.produit',
            'user'
        ])
            ->where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->orderBy('created_at', 'DESC')->get();
        return response()->json($ventes, 200);
    }

    // Pour la liste des element dans l'historique :
    public function historiqueVenteSelected($id)
    {
        ["id" => $id];
        $boutique_id = $this->getBoutiqueId();
        $ventes = Vente::with([
            'detailVentes.produit'
        ])->where('id', $id)
            ->where('boutique_id', $boutique_id)
            ->orderBy('date_vente', 'DESC')->get();
        return response()->json($ventes, 200);
    }

    // Pour le chiffre d'affaire global et détaillé
    public function chiffre()
    {
        $boutique_id = $this->getBoutiqueId();

        // Chiffre d'affaires par année
        $parAnnee = Vente::where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->selectRaw("strftime('%Y', date_vente) as annee, SUM(montant_total) as ca")
            ->groupByRaw("strftime('%Y', date_vente)")
            ->orderByRaw("strftime('%Y', date_vente) DESC")
            ->get();

        // Chiffre d'affaires par mois (Détail de l'année en cours ou dernière année)
        $currentYear = now()->year;
        $parMois = DB::table('ventes')
            ->join('detail_ventes', 'ventes.id', '=', 'detail_ventes.vente_id')
            ->where('ventes.boutique_id', $boutique_id)
            ->where('ventes.statut', 'validee')
            ->whereYear('ventes.date_vente', $currentYear)
            ->select(
                DB::raw("strftime('%m', ventes.date_vente) as mois_num"),
                DB::raw('SUM(detail_ventes.montant_total) as ca'),
                DB::raw('SUM(detail_ventes.quantite) as qtv')
            )
            ->groupBy(DB::raw("strftime('%m', ventes.date_vente)"))
            ->orderBy(DB::raw("strftime('%m', ventes.date_vente)"), 'DESC')
            ->get();

        return response()->json([
            'par_annee' => $parAnnee,
            'par_mois' => $parMois,
            'annee_actuelle' => $currentYear
        ], 200);
    }

    public function chiffreAffiare()
    {
        $boutique_id = $this->getBoutiqueId();
        $chiffre = Vente::where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->selectRaw("strftime('%Y', date_vente) as annee, SUM(montant_total) as total")
            ->groupByRaw("strftime('%Y', date_vente)")
            ->orderByRaw("strftime('%Y', date_vente)")
            ->get();
        return response()->json($chiffre, 200);
    }

    public function getAnneeVente()
    {
        $boutique_id = $this->getBoutiqueId();
        $annee = Vente::where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->selectRaw("strftime('%Y', date_vente) as annee")
            ->groupByRaw("strftime('%Y', date_vente)")
            ->orderByRaw("strftime('%Y', date_vente) DESC")
            ->get();
        return response()->json($annee, 200);
    }

    public function getVenteByAnnee($annee)
    {
        $boutique_id = $this->getBoutiqueId();
        $vente = DB::table('ventes')
            ->join('detail_ventes', 'ventes.id', '=', 'detail_ventes.vente_id')
            ->where('ventes.boutique_id', $boutique_id)
            ->where('ventes.statut', 'validee')
            ->whereYear('ventes.date_vente', $annee)
            ->select(
                DB::raw("strftime('%m', ventes.date_vente) as mois_num"),
                DB::raw('SUM(detail_ventes.montant_total) as ca'),
                DB::raw('SUM(detail_ventes.quantite) as qtv')
            )
            ->groupBy(DB::raw("strftime('%m', ventes.date_vente)"))
            ->orderBy(DB::raw("strftime('%m', ventes.date_vente)"), 'DESC')
            ->get();

        return response()->json($vente, 200);
    }
    public function getVenteByMois($annee, $mois)
    {
        $boutique_id = $this->getBoutiqueId();
        $venteAnneeMois = Vente::where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->selectRaw("strftime('%d', date_vente) as jour, SUM(montant_total) as total")
            ->whereRaw("strftime('%Y', date_vente) = ? AND strftime('%m', date_vente) = ?", [$annee, sprintf("%02d", $mois)])
            ->groupByRaw("strftime('%d', date_vente)")
            ->orderByRaw("strftime('%d', date_vente)")
            ->get();
        return response()->json($venteAnneeMois, 200);
    }

    // Nombre Pour le tableau de board

    // Pour la liste des ventes et les details en fonction de la date:
    public function nBventeDateJour($year, $month, $day)
    {
        $boutique_id = $this->getBoutiqueId();
        $results = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->whereYear('date_vente', $year)
            ->whereMonth('date_vente', $month)
            ->whereDay('date_vente', $day)
            ->selectRaw('COUNT(*) as nombre, SUM(montant_total) as total')
            ->first();

        return response()->json([$results], 200);
    }

    public function nBventeDateMois($year, $month)
    {
        $boutique_id = $this->getBoutiqueId();
        $results = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->whereYear('date_vente', $year)
            ->whereMonth('date_vente', $month)
            ->selectRaw('COUNT(*) as nombre, SUM(montant_total) as total')
            ->first();

        return response()->json([$results], 200);
    }

    public function nBventeDateAnnee($year)
    {
        $boutique_id = $this->getBoutiqueId();
        $results = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->whereYear('date_vente', $year)
            ->selectRaw('COUNT(*) as nombre, SUM(montant_total) as total')
            ->first();

        return response()->json([$results], 200);
    }

    public function clientCount()
    {
        $boutique_id = $this->getBoutiqueId();
        $count = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->distinct('client_id')
            ->count('client_id');

        return response()->json(['count' => $count], 200);
    }

    public function getSummary()
    {
        $boutique_id = $this->getBoutiqueId();

        $produitCount = DB::table('stocks')
            ->where('boutique_id', $boutique_id)
            ->where('quantite', '>', 0)
            ->count();

        $categorieCount = DB::table('categories')->count();

        $totalStock = DB::table('stocks')
            ->where('boutique_id', $boutique_id)
            ->sum('quantite');

        // On peut aussi ajouter le nombre de ventes du jour pour le ratio si besoin
        $ventesJour = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->whereDate('date_vente', now())
            ->count();

        return response()->json([
            'produit_count' => $produitCount,
            'categorie_count' => $categorieCount,
            'total_stock' => $totalStock,
            'ventes_jour' => $ventesJour,
            'annee_active' => now()->year
        ], 200);
    }


    // Les ventes les plus recentes en fonction de la date qui est dans les details de vente:
    public function recentVente()
    {
        $boutique_id = $this->getBoutiqueId();
        $ventes = DetailVente::with(['produit.categorie'])
            ->whereHas('vente', function ($q) use ($boutique_id) {
                $q->where('boutique_id', $boutique_id)
                    ->where('statut', 'validee');
            })
            ->selectRaw('detail_ventes.*,SUM(quantite) as quantite')
            ->groupBy('produit_id')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        return response()->json($ventes, 200);
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
        //Pour l'enregistrement d'une vente:
        $request->validate([
            'produits' => 'required|array',
            'montant_total' => 'required|numeric',
            'date' => 'required|date',
            'remise' => 'required|numeric',
        ]);
        $client_id = 1;
        $user = Auth::user();
        $boutique_id = $this->getBoutiqueId();

        if (!$boutique_id) {
            return response()->json(['message' => 'Boutique non identifiée. Veuillez sélectionner une boutique.'], 400);
        }

        // On verifier si c'est avec un client ou pas:
        if ($request->client_nom) {
            $client_nom = strtoupper($request->input('client_nom'));
            $client = Client::where('nom', $client_nom)->first();
            if (!$client) {
                $client = new Client();
                $client->nom = $client_nom;
                $client->telephone = $request->input('client_numero');
                $client->save();
            }
            $client_id = $client->id;
        }

        // On cree une nouvelle vente
        $vente = new Vente();
        $vente->client_id = $client_id;
        $vente->boutique_id = $boutique_id;
        $vente->user_id = $user->id;
        $vente->montant_total = $request->montant_total;
        $vente->date_vente = now()->format('Y-m-d');
        $vente->statut = 'validee';
        $vente->save();

        $venteId = $vente->id;
        $remise = $request->remise ?? 0;

        $produits = $request->produits;
        foreach ($produits as $produit) {
            $pId = $produit['produits']['id'];
            $pNom = $produit['produits']['nom'];

            // Mettre à jour le stock
            $stock = Stock::where('produit_id', $pId)
                ->where('boutique_id', $boutique_id)
                ->first();

            if (!$stock || $stock->quantite < $produit['quantite']) {
                $vente->delete(); // Rollback simple
                return response()->json(['message' => "Stock insuffisant pour $pNom"], 400);
            }

            $stock->quantite -= $produit['quantite'];
            $stock->save();

            $detailVente = new DetailVente();
            $detailVente->vente_id = $venteId;
            $detailVente->produit_id = $pId;
            $detailVente->quantite = $produit['quantite'];
            $detailVente->quantite_restante = $stock->quantite;

            $prixUnitaire = $produit['prix'] ?? $produit['produits']['stock']['prix_vente'];
            $detailVente->prix_unitaire = $prixUnitaire;
            $detailVente->montant = $prixUnitaire * $produit['quantite'];
            $detailVente->remise = $remise;
            $detailVente->montant_total = $detailVente->montant;
            $detailVente->montant_paye = $detailVente->montant;
            $detailVente->save();

            // Inventaire
            Inventaire::create([
                'produit_id' => $pId,
                'boutique_id' => $boutique_id,
                'user_id' => $user->id,
                'quantite' => $produit['quantite'],
                'type' => 'retrait',
                'description' => 'Vente du produit ' . $pNom,
                'date' => now()->format('Y-m-d')
            ]);
        }

        $statutFacture = $request->statut ?? "payée";

        $facture = new Facture();
        $facture->client_id = $client_id;
        $facture->boutique_id = $boutique_id;
        $facture->montant_total = $request->montant_total;
        $facture->date_facturation = now()->format('Y-m-d');
        $facture->statut = $statutFacture;
        $facture->description = 'Facture de vente #' . $venteId;
        $facture->save();

        $factureVente = new FactureVente();
        $factureVente->facture_id = $facture->id;
        $factureVente->vente_id = $venteId;
        $factureVente->save();

        return response()->json(['message' => 'Vente effectuée avec succès', 'factID' => $facture->id], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vente $vente)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vente $vente)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vente $vente)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vente $vente)
    {
        return $this->supprimerVente($vente->id);
    }
}
