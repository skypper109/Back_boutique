<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Vente;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Facture;
use App\Models\Produit;
use App\Models\Inventaire;
use App\Models\DetailVente;
use App\Models\FactureVente;
use Illuminate\Http\Request;
use App\Models\PaiementCredit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

    public function topVente()
    {
        $boutique_id = $this->getBoutiqueId();
        $topVente = DetailVente::with('produit')
            ->whereHas('produit')
            ->whereHas('vente', function ($q) use ($boutique_id) {
                $q->where('boutique_id', $boutique_id)
                    ->whereIn('statut', ['validee', 'payee', 'credit']);
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
            ->whereHas('produit')
            ->whereHas('vente', function ($q) use ($boutique_id) {
                $q->where('boutique_id', $boutique_id)
                    ->whereIn('statut', ['validee', 'payee', 'credit']);
            })
            ->selectRaw('produit_id, prix_unitaire, SUM(quantite) as total_quantite, SUM(montant_total) as total_montant')
            ->groupBy('produit_id', 'prix_unitaire')
            ->orderByDesc('total_quantite')
            ->limit($limit)
            ->get();
        return response()->json($topVente, 200);
    }

    public function modifierVente(Request $request)
    {
        $request->validate([
            'vente_id' => 'required|integer',
            'produits' => 'required|array',
        ]);

        $venteId = $request->vente_id;
        $vente = Vente::findOrFail($venteId);
        $user = Auth::user();
        $boutique_id = $this->getBoutiqueId();

        if (!$boutique_id) {
            return response()->json(['message' => 'Boutique non identifiée'], 400);
        }

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
                $stock = Stock::where('produit_id', $produitId)
                    ->where('boutique_id', $boutique_id)
                    ->first();

                if ($stock) {
                    $stock->quantite += $difference;
                    $stock->save();
                }

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
                    $detailVente->montant_total = $detailVente->montant;
                    $detailVente->montant_paye = $detailVente->montant;
                    $detailVente->save();
                    $nouveauMontantTotal += $detailVente->montant;
                }
            } else {
                $nouveauMontantTotal += $detailVente->montant;
            }
        }

        if ($nouveauMontantTotal <= 0) {
            if ($vente->type_paiement == 'credit') {
                // If payments were already made, refund them
                $credits = PaiementCredit::where('vente_id', $venteId)
                    ->where('boutique_id', $boutique_id)
                    ->get();
                $montant_total_paye = $credits->sum('montant');
                if ($montant_total_paye > 0) {
                    Expense::create([
                        'type' => "",
                        'boutique_id' => $boutique_id,
                        'user_id' => $user->id,
                        'montant' => $montant_total_paye,
                        'date' => now()->format('Y-m-d'),
                        'description' => "Remboursement suite au retour complet de la vente #" . $venteId,
                    ]);
                }

                $vente->montant_restant = 0;
                $vente->statut = 'Credit annule';
            } else {
                $vente->statut = 'annulee';
            }
            $vente->save();

            return response()->json(['message' => 'Vente annulée car tous les produits ont été retournés'], 200);
        }

        if ($vente->type_paiement == 'credit') {
            $ancienMontantTotal = $vente->montant_total;
            $vente->montant_total = $nouveauMontantTotal;
            $valeurRetour = $ancienMontantTotal - $nouveauMontantTotal;
            $ancienRestant = $vente->montant_restant;

            if ($valeurRetour > $ancienRestant) {
                // Le montant du retour dépasse la dette actuelle
                $surplusARembourser = $valeurRetour - $ancienRestant;
                
                Expense::create([
                    'type' => "Remboursement Retour",
                    'boutique_id' => $boutique_id,
                    'user_id' => $user->id,
                    'montant' => $surplusARembourser,
                    'date' => now()->format('Y-m-d'),
                    'description' => "Remboursement de surplus (Rendu au client) suite au retour produit sur la vente à crédit #" . $venteId . ". Retour: " . number_format($valeurRetour, 0, '.', ' ') . " FCFA, Dette soldée: " . number_format($ancienRestant, 0, '.', ' ') . " FCFA.",
                ]);

                $vente->montant_restant = 0;
                $vente->statut = 'payee';
                // $vente->montant_statut = 'Credit paye';
            } else {
                // On réduit simplement la dette
                $vente->montant_restant = $ancienRestant - $valeurRetour;
                if ($vente->montant_restant == 0) {
                    $vente->statut = 'payee';
                    // $vente->montant_statut = 'Credit paye';
                }
            }
        } else {
            $vente->montant_total = $nouveauMontantTotal;
        }
        $vente->save();

        return response()->json(['message' => 'Vente modifiée avec succès', 'nouveau_montant' => $nouveauMontantTotal], 200);
    }

    public function supprimerVente($id)
    {
        $boutique_id = $this->getBoutiqueId();
        $vente = Vente::with('detailVentes')->where('id', $id)
            ->where('boutique_id', $boutique_id)
            ->firstOrFail();

        if ($vente->statut === 'annulee') {
            return response()->json(['message' => 'Cette vente est déjà annulée'], 400);
        }

        foreach ($vente->detailVentes as $detail) {
            $stock = Stock::where('produit_id', $detail->produit_id)
                ->where('boutique_id', $boutique_id)
                ->first();
            if ($stock) {
                $stock->quantite += $detail->quantite;
                $stock->save();
            }

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

    public function annuleVente($id)
    {
        $user = Auth::user();
        $boutique_id = $this->getBoutiqueId();

        $detailVente = DetailVente::with('vente')->where('id', $id)->firstOrFail();

        if ($detailVente->vente->boutique_id != $boutique_id) {
            return response()->json(["message" => "Action non autorisée"], 403);
        }

        $qte = $detailVente->quantite;
        $mtt = $detailVente->montant;
        $idProduit = $detailVente->produit_id;

        $vente = $detailVente->vente;
        $vente->montant_total -= $mtt;
        $vente->save();

        $stock = Stock::where('produit_id', $idProduit)
            ->where('boutique_id', $boutique_id)
            ->first();

        if ($stock) {
            $stock->quantite += $qte;
            $stock->save();
        }

        $produit = Produit::where('id', $idProduit)->first();

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
            'user',
            'client'
        ])
            ->where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->orderBy('created_at', 'DESC')->get();
        return response()->json($ventes, 200);
    }

    public function getProformas()
    {
        $boutique_id = $this->getBoutiqueId();
        $proformas = Vente::with(['client', 'user', 'detailVentes.produit'])
            ->where('boutique_id', $boutique_id)
            ->where('statut', 'proforma')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($proformas);
    }

    public function historiqueVenteSelected($id)
    {
        $boutique_id = $this->getBoutiqueId();
        $ventes = Vente::with([
            'detailVentes.produit',
            'client',
            'user'
        ])->where('id', $id)
            ->where('boutique_id', $boutique_id)
            ->get();
        return response()->json($ventes, 200);
    }

    public function chiffre()
    {
        $boutique_id = $this->getBoutiqueId();

        $parAnnee = Vente::where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->selectRaw("YEAR(date_vente) as annee, SUM(montant_total) as ca")
            ->groupByRaw("YEAR(date_vente)")
            ->orderByRaw("YEAR(date_vente) DESC")
            ->get();

        $currentYear = now()->year;
        $parMois = DB::table('ventes')
            ->join('detail_ventes', 'ventes.id', '=', 'detail_ventes.vente_id')
            ->where('ventes.boutique_id', $boutique_id)
            ->whereIn('ventes.statut', ['validee', 'payee', 'credit'])
            ->whereRaw("YEAR(ventes.date_vente) = ?", [$currentYear])
            ->select(
                DB::raw("MONTH(ventes.date_vente) as mois_num"),
                DB::raw('SUM(detail_ventes.montant_total) as ca'),
                DB::raw('SUM(detail_ventes.quantite) as qtv')
            )
            ->groupBy(DB::raw("MONTH(ventes.date_vente)"))
            ->orderBy(DB::raw("MONTH(ventes.date_vente)"), 'DESC')
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
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->selectRaw("YEAR(date_vente) as annee, SUM(montant_total) as total")
            ->groupByRaw("YEAR(date_vente)")
            ->orderByRaw("YEAR(date_vente)")
            ->get();
        return response()->json($chiffre, 200);
    }

    public function getAnneeVente()
    {
        $boutique_id = $this->getBoutiqueId();
        $annee = Vente::where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->selectRaw("YEAR(date_vente) as annee")
            ->groupByRaw("YEAR(date_vente)")
            ->orderByRaw("YEAR(date_vente) DESC")
            ->get();
        return response()->json($annee, 200);
    }

    public function getVenteByAnnee($annee)
    {
        $boutique_id = $this->getBoutiqueId();
        $vente = DB::table('ventes')
            ->join('detail_ventes', 'ventes.id', '=', 'detail_ventes.vente_id')
            ->where('ventes.boutique_id', $boutique_id)
            ->whereIn('ventes.statut', ['validee', 'payee', 'credit'])
            ->whereRaw("YEAR(ventes.date_vente) = ?", [$annee])
            ->select(
                DB::raw("MONTH(ventes.date_vente) as mois_num"),
                DB::raw('SUM(detail_ventes.montant_total) as ca'),
                DB::raw('SUM(detail_ventes.quantite) as qtv')
            )
            ->groupBy(DB::raw("MONTH(ventes.date_vente)"))
            ->orderBy(DB::raw("MONTH(ventes.date_vente)"), 'DESC')
            ->get();

        return response()->json($vente, 200);
    }

    public function getVenteByMois($annee, $mois)
    {
        $boutique_id = $this->getBoutiqueId();
        $venteAnneeMois = Vente::where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->selectRaw("DAY(date_vente) as jour, SUM(montant_total) as total")
            ->whereRaw("YEAR(date_vente) = ? AND MONTH(date_vente) = ?", [$annee, $mois])
            ->groupByRaw("DAY(date_vente)")
            ->orderByRaw("DAY(date_vente)")
            ->get();
        return response()->json($venteAnneeMois, 200);
    }

    public function nBventeDateJour($year, $month, $day)
    {
        $boutique_id = $this->getBoutiqueId();
        $results = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->whereRaw("YEAR(date_vente) = ? AND MONTH(date_vente) = ? AND DAY(date_vente) = ?", [
                $year,
                $month,
                $day
            ])
            ->selectRaw('COUNT(*) as nombre, SUM(montant_total) as total')
            ->first();

        return response()->json([$results], 200);
    }

    public function nBventeDateMois($year, $month)
    {
        $boutique_id = $this->getBoutiqueId();
        $results = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->whereRaw("YEAR(date_vente) = ? AND MONTH(date_vente) = ?", [
                $year,
                $month
            ])
            ->selectRaw('COUNT(*) as nombre, SUM(montant_total) as total')
            ->first();

        return response()->json([$results], 200);
    }

    public function nBventeDateAnnee($year)
    {
        $boutique_id = $this->getBoutiqueId();
        $results = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->whereRaw("YEAR(date_vente) = ?", [$year])
            ->selectRaw('COUNT(*) as nombre, SUM(montant_total) as total')
            ->first();

        return response()->json([$results], 200);
    }

    public function clientCount()
    {
        $boutique_id = $this->getBoutiqueId();
        $count = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
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

        $ventesJour = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->whereRaw("date_vente = ?", [now()->format('Y-m-d')])
            ->count();

        return response()->json([
            'produit_count' => $produitCount,
            'categorie_count' => $categorieCount,
            'total_stock' => $totalStock,
            'ventes_jour' => $ventesJour,
            'annee_active' => now()->year
        ], 200);
    }

    public function recentVente()
    {
        $boutique_id = $this->getBoutiqueId();
        $ventes = DetailVente::with(['produit.categorie'])
            ->whereHas('produit')
            ->whereHas('vente', function ($q) use ($boutique_id) {
                $q->where('boutique_id', $boutique_id)
                    ->whereIn('statut', ['validee', 'payee', 'credit']);
            })
            ->selectRaw('detail_ventes.*,SUM(quantite) as quantite')
            ->groupBy('produit_id')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        return response()->json($ventes, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'produits' => 'required|array',
            'montant_total' => 'required|numeric',
            'date' => 'date',
            'remise' => 'required|numeric',
        ]);

        $user = Auth::user();
        $boutique_id = $this->getBoutiqueId();

        if (!$boutique_id) {
            return response()->json(['message' => 'Boutique non identifiée.'], 400);
        }

        $client_id = 1;
        if ($request->client_nom) {
            $client_nom = strtoupper($request->client_nom);
            $client = Client::where('telephone', $request->client_numero)->first();
            if (!$client) {
                $client = Client::create([
                    'nom' => $client_nom,
                    'telephone' => $request->client_numero
                ]);
            }
            $client_id = $client->id;
        }

        $is_proforma = $request->input('is_proforma', false);

        DB::beginTransaction();
        try {
            $vente = new Vente();
            $vente->client_id = $client_id;
            $vente->boutique_id = $boutique_id;
            $vente->user_id = $user->id;
            $vente->montant_total = $request->montant_total;
            $vente->type_paiement = $is_proforma ? 'proforma' : ($request->type_paiement ?? 'contant');
            $vente->montant_avance = $request->montant_avance ?? 0;

            if ($is_proforma) {
                $vente->statut = 'proforma';
                $vente->montant_restant = $request->montant_total;
            } else {
                $vente->statut = ($vente->type_paiement === 'credit') ? 'credit' : 'payee';
                $vente->montant_restant = ($vente->type_paiement === 'credit')
                    ? ($request->montant_total - $vente->montant_avance)
                    : 0;
            }

            $vente->date_vente = $request->input('date') ?? now()->format('Y-m-d');
            $vente->save();

            foreach ($request->produits as $item) {
                // Robust ID extraction (handles both {produits: {id: 1}} and {id: 1})
                $pId = isset($item['produits']['id']) ? $item['produits']['id'] : ($item['id'] ?? null);

                if (!$pId) {
                    throw new \Exception("ID produit manquant dans la requête");
                }

                $pNom = isset($item['produits']['nom']) ? $item['produits']['nom'] : ($item['nom'] ?? "Produit #$pId");

                $stock = Stock::where('produit_id', $pId)
                    ->where('boutique_id', $boutique_id)
                    ->first();

                if (!$is_proforma) {
                    if (!$stock || $stock->quantite < $item['quantite']) {
                        throw new \Exception("Stock insuffisant pour $pNom");
                    }
                    $stock->quantite -= $item['quantite'];
                    $stock->save();
                }

                $detail = new DetailVente();
                $detail->vente_id = $vente->id;
                $detail->produit_id = $pId;
                $detail->quantite = $item['quantite'];

                // Robust price extraction
                $prixU = $item['prix'] ?? ($item['produits']['stock']['prix_vente'] ?? ($stock->prix_vente ?? 0));

                $detail->prix_unitaire = $prixU;
                $detail->montant = $prixU * $item['quantite'];
                $detail->remise = $request->remise ?? 0;
                $detail->montant_total = $item['montant_total'] ?? $detail->montant;
                $detail->montant_paye = $detail->montant_total;
                $detail->quantite_restante = $item['quantite'];
                $detail->save();

                if (!$is_proforma) {
                    Inventaire::create([
                        'produit_id' => $pId,
                        'boutique_id' => $boutique_id,
                        'user_id' => $user->id,
                        'quantite' => $item['quantite'],
                        'type' => 'retrait',
                        'description' =>$vente->statut == 'credit' ? "Produit ".$pNom . " vendu a credit " : "Vente du produit # ".(isset($item['produits']['reference']) ? $item['produits']['reference'] : ($item['reference'] ?? "Produit #$pId")),
                        'date' => now()->format('Y-m-d')
                    ]);
                }
            }

            if (!$is_proforma) {
                $facture = Facture::create([
                    'client_id' => $client_id,
                    'boutique_id' => $boutique_id,
                    'montant_total' => $request->montant_total,
                    'date_facturation' => now()->format('Y-m-d'),
                    'statut' => ($vente->type_paiement === 'credit' ? 'en attente' : 'payée'),
                    'description' => 'Facture Vente #' . $vente->id
                ]);

                FactureVente::create([
                    'facture_id' => $facture->id,
                    'vente_id' => $vente->id
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => $is_proforma ? 'Pro-forma généré' : 'Vente effectuée',
                'venteID' => $vente->id,
                'factID' => isset($facture) ? $facture->id : null
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function convertProformaToSale(Request $request, $id)
    {
        $boutique_id = $this->getBoutiqueId();
        $vente = Vente::with('detailVentes')->where('id', $id)
            ->where('boutique_id', $boutique_id)
            ->where('statut', 'proforma')
            ->firstOrFail();

        $user = Auth::user();
        $type_paiement = $request->input('type_paiement', 'contant');
        $montant_avance = $request->input('montant_avance', 0);

        DB::beginTransaction();
        try {
            // 1. Stock Check & Deduction
            foreach ($vente->detailVentes as $detail) {
                $stock = Stock::where('produit_id', $detail->produit_id)
                    ->where('boutique_id', $boutique_id)
                    ->first();

                if (!$stock || $stock->quantite < $detail->quantite) {
                    $pNom = Produit::find($detail->produit_id)->nom ?? 'Produit inconnu';
                    throw new \Exception("Stock insuffisant pour $pNom ($stock->quantite disponible)");
                }

                $stock->quantite -= $detail->quantite;
                $stock->save();

                // Log Inventory
                Inventaire::create([
                    'produit_id' => $detail->produit_id,
                    'boutique_id' => $boutique_id,
                    'user_id' => $user->id,
                    'quantite' => $detail->quantite,
                    'type' => 'retrait',
                    'description' => 'Conversion Pro-forma #' . $vente->id . ' vers Vente',
                    'date' => now()->format('Y-m-d')
                ]);
            }

            // 2. Update Vente Identity
            $vente->type_paiement = $type_paiement;
            $vente->montant_avance = $montant_avance;
            $vente->statut = ($type_paiement === 'credit') ? 'credit' : 'payee';
            $vente->montant_restant = ($type_paiement === 'credit')
                ? ($vente->montant_total - $montant_avance)
                : 0;
            $vente->date_vente = now()->format('Y-m-d');
            $vente->save();

            // 3. Generate Facture
            $facture = Facture::create([
                'client_id' => $vente->client_id,
                'boutique_id' => $boutique_id,
                'montant_total' => $vente->montant_total,
                'date_facturation' => now()->format('Y-m-d'),
                'statut' => ($type_paiement === 'credit' ? 'en attente' : 'payée'),
                'description' => 'Facture issue de Pro-forma #' . $vente->id
            ]);

            FactureVente::create([
                'facture_id' => $facture->id,
                'vente_id' => $vente->id
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Conversion réussie',
                'venteID' => $vente->id,
                'factID' => $facture->id
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(Vente $vente)
    {
        return $this->supprimerVente($vente->id);
    }
}
