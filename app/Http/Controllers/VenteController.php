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
            'remise' => 'numeric',
        ]);

        $venteId = $request->vente_id;
        $vente = Vente::findOrFail($venteId);
        $user = Auth::user();
        $boutique_id = $this->getBoutiqueId();

        if (!$boutique_id) {
            return response()->json(['message' => 'Boutique non identifiée'], 400);
        }

        $nouveauMontantBrut = 0;
        $nouvelleRemiseTotale = $request->input('remise', 0); // User can adjust global remise too

        DB::beginTransaction();
        try {
            foreach ($request->produits as $produitData) {
                // Handle different payload structures
                $produitId = $produitData['produit']['id'] ?? $produitData['produit_id'];
                $nouvelleQte = $produitData['quantite'];
                $nouveauPrixU = $produitData['prix_unitaire'] ?? null;
                $nouvelleRemiseLigne = $produitData['remise'] ?? 0;

                $detailVente = DetailVente::where('vente_id', $venteId)
                    ->where('produit_id', $produitId)
                    ->first();

                if (!$detailVente) continue;

                $ancienneQte = $detailVente->quantite;
                $ancienPrixU = $detailVente->prix_unitaire;
                $ancienneRemiseLigne = $detailVente->remise;

                $difference = $ancienneQte - $nouvelleQte;
                $diffRemise = $ancienneRemiseLigne - $nouvelleRemiseLigne;
                $diffPrix = ($nouveauPrixU !== null) ? ($nouveauPrixU - $ancienPrixU) : 0;

                // Update unit price if provided
                if ($nouveauPrixU !== null) {
                    $detailVente->prix_unitaire = $nouveauPrixU;
                }

                if ($difference != 0 || $diffRemise != 0 || $diffPrix != 0) {
                    $stock = Stock::where('produit_id', $produitId)
                        ->where('boutique_id', $boutique_id)
                        ->first();

                    if ($difference != 0 && $stock) {
                        $stock->quantite += $difference;
                        $stock->save();
                    }

                    $remiseAudit = $diffRemise + ($diffPrix * $nouvelleQte); // Correction for profit impact

                    Inventaire::create([
                        'produit_id' => $produitId,
                        'boutique_id' => $boutique_id,
                        'user_id' => $user->id,
                        'vente_id' => $vente->id,
                        'quantite' => abs($difference),
                        'type' => $difference >= 0 ? 'ajout' : 'retrait',
                        'prix_achat' => $stock ? $stock->prix_achat : 0,
                        'prix_vente' => $nouveauPrixU ?? $detailVente->prix_unitaire,
                        'remise' => $remiseAudit, 
                        'description' => ($difference > 0 ? 'Retour Client (Audit)' : ($difference < 0 ? 'Ajustement Vente' : 'Correction Prix/Remise')) . ' sur Vente #' . $venteId,
                        'date' => now()
                    ]);
                }

                if ($nouvelleQte <= 0) {
                    $detailVente->delete();
                } else {
                    $detailVente->quantite = $nouvelleQte;
                    $detailVente->prix_unitaire = $nouveauPrixU ?? $detailVente->prix_unitaire;
                    $detailVente->remise = $nouvelleRemiseLigne;
                    $detailVente->montant = $detailVente->prix_unitaire * $nouvelleQte;
                    $detailVente->montant_total = $detailVente->montant;
                    $detailVente->montant_paye = $detailVente->montant - $nouvelleRemiseLigne;
                    $detailVente->save();
                    
                    $nouveauMontantBrut += $detailVente->montant;
                }
            }

            // Delta for the Global Extra Reduction
            $ancienneRemiseTotale = (float)$vente->remise;
            $diffRemiseGlobale = $ancienneRemiseTotale - $nouvelleRemiseTotale;

            if ($diffRemiseGlobale != 0) {
                Inventaire::create([
                    'produit_id' => null,
                    'boutique_id' => $boutique_id,
                    'user_id' => $user->id,
                    'vente_id' => $vente->id,
                    'quantite' => 0,
                    'type' => $diffRemiseGlobale >= 0 ? 'ajout' : 'retrait',
                    'prix_achat' => 0,
                    'prix_vente' => 0,
                    'remise' => $diffRemiseGlobale,
                    'description' => 'Ajustement Remise Globale (Audit Vente #' . $venteId . ')',
                    'date' => now()
                ]);
            }

            // Recalculate Global Sale Total
            $montantFinalApresRemise = $nouveauMontantBrut - $nouvelleRemiseTotale;

            if ($nouveauMontantBrut <= 0) {
                // Total cancellation logic...
                if ($vente->type_paiement == 'credit') {
                    $credits = PaiementCredit::where('vente_id', $venteId)->get();
                    $montant_total_paye = $credits->sum('montant');
                    if ($montant_total_paye > 0) {
                        Expense::create([
                            'type' => "Remboursement Retour",
                            'boutique_id' => $boutique_id,
                            'user_id' => $user->id,
                            'montant' => $montant_total_paye,
                            'date' => now(),
                            'description' => "Remboursement suite au retour complet de la vente #" . $venteId,
                        ]);
                    }
                    $vente->montant_restant = 0;
                    $vente->statut = 'Credit annule';
                } else {
                    $vente->statut = 'annulee';
                }
                $vente->montant_total = 0;
                $vente->remise = 0;
            } else {
                if ($vente->type_paiement == 'credit') {
                    $ancienNet = $vente->montant_total;
                    $valeurRetour = $ancienNet - $montantFinalApresRemise;
                    $ancienRestant = $vente->montant_restant;

                    if ($valeurRetour > $ancienRestant) {
                        $surplus = $valeurRetour - $ancienRestant;
                        Expense::create([
                            'type' => "Remboursement Retour",
                            'boutique_id' => $boutique_id,
                            'user_id' => $user->id,
                            'montant' => $surplus,
                            'date' => now(),
                            'description' => "Remboursement surplus audit sur vente #" . $venteId,
                        ]);
                        $vente->montant_restant = 0;
                        $vente->statut = 'payee';
                    } else {
                        $vente->montant_restant = $ancienRestant - $valeurRetour;
                        if ($vente->montant_restant <= 0) $vente->statut = 'payee';
                    }
                }
                $vente->montant_total = $montantFinalApresRemise;
                $vente->remise = $nouvelleRemiseTotale;
            }

            $vente->save();
            DB::commit();

            return response()->json(['message' => 'Vente rectifiée avec succès', 'nouveau_montant' => $montantFinalApresRemise], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
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
                'vente_id' => $vente->id,
                'quantite' => $detail->quantite,
                'type' => 'ajout',
                'prix_achat' => $stock ? $stock->prix_achat : 0,
                'prix_vente' => $detail->prix_unitaire,
                'remise' => 0, // Reset remise on total cancellation
                'description' => 'Annulation de Vente #' . $vente->id,
                'date' => now()
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

        $vente = Vente::with('detailVentes')->where('id', $id)
            ->where('boutique_id', $boutique_id)
            ->firstOrFail();

        if ($vente->statut === 'annulee') {
            return response()->json(['message' => 'Cette vente est déjà annulée'], 400);
        }

        DB::beginTransaction();
        try {
            $originalRemise = (float)$vente->remise;
            $sumLineRemises = 0;

            foreach ($vente->detailVentes as $detail) {
                $stock = Stock::where('produit_id', $detail->produit_id)
                    ->where('boutique_id', $boutique_id)
                    ->first();
                
                if ($stock) {
                    $stock->quantite += $detail->quantite;
                    $stock->save();
                }

                $produit = Produit::find($detail->produit_id);
                $lineRemise = (float)($detail->remise ?? 0);
                $sumLineRemises += $lineRemise;

                Inventaire::create([
                    'produit_id' => $detail->produit_id,
                    'boutique_id' => $boutique_id,
                    'user_id' => $user->id,
                    'vente_id' => $vente->id,
                    'quantite' => $detail->quantite,
                    'type' => 'ajout',
                    'prix_achat' => $stock ? $stock->prix_achat : 0,
                    'prix_vente' => $detail->prix_unitaire,
                    'remise' => $lineRemise, // FIXED: Correct line reduction
                    'description' => 'Annulation de Vente #' . $vente->id . ' (' . ($produit ? $produit->nom : 'Produit inconnu') . ')',
                    'date' => now()
                ]);
            }

            // Global discount cancellation
            $extraReduction = $originalRemise - $sumLineRemises;
            if ($extraReduction != 0) {
                Inventaire::create([
                    'produit_id' => null,
                    'boutique_id' => $boutique_id,
                    'user_id' => $user->id,
                    'vente_id' => $vente->id,
                    'quantite' => 0,
                    'type' => 'ajout',
                    'prix_achat' => 0,
                    'prix_vente' => 0,
                    'remise' => $extraReduction,
                    'description' => 'Annulation Remise Globale (Vente #' . $vente->id . ')',
                    'date' => now()
                ]);
            }

            // Gestion des paiements si c'est une vente à crédit
            if ($vente->type_paiement === 'credit') {
                $credits = PaiementCredit::where('vente_id', $vente->id)
                    ->where('boutique_id', $boutique_id)
                    ->get();
                
                $montant_total_paye = $credits->sum('montant');
                
                if ($montant_total_paye > 0) {
                    Expense::create([
                        'type' => "Remboursement Retour",
                        'boutique_id' => $boutique_id,
                        'user_id' => $user->id,
                        'montant' => $montant_total_paye,
                        'date' => now(),
                        'description' => "Remboursement suite à l'annulation de la vente à crédit #" . $vente->id,
                    ]);
                }
                $vente->montant_restant = 0;
            }

            $vente->montant_total = 0;
            $vente->remise = 0;
            $vente->statut = 'annulee';
            $vente->save();

            DB::commit();
            return response()->json(["message" => "Vente Annulée avec succès"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["message" => "Erreur lors de l'annulation: " . $e->getMessage()], 500);
        }
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
        $currentYear = now()->year;

        // 1. Annual Summary (Simple CA from Ventes table for the list)
        $parAnnee = Vente::where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->selectRaw("YEAR(date_vente) as annee, SUM(montant_total) as ca")
            ->groupByRaw("YEAR(date_vente)")
            ->orderByRaw("YEAR(date_vente) DESC")
            ->get();

        // 2. Monthly Detail (More precise, including returns and costs)
        // We calculate this from Inventaire to be consistent with the audit page
        // We filter out inventory entries linked to cancelled sales
        $mouvements = Inventaire::with(['produit.stock', 'vente'])
            ->where('boutique_id', $boutique_id)
            ->whereYear('date', $currentYear)
            ->whereNotNull('vente_id')
            ->whereHas('vente', function($q) {
                $q->whereIn('statut', ['validee', 'payee', 'credit']);
            })
            ->get();

        $statsMensuelles = [];
        // Initialize 12 months
        for ($i = 1; $i <= 12; $i++) {
            $statsMensuelles[$i] = [
                'mois_num' => $i,
                'ca' => 0,
                'qtv' => 0,
                'cout_achat' => 0,
                'remises_traitees' => [] // To track global remise per vente_id
            ];
        }

        foreach ($mouvements as $mov) {
            $m = \Carbon\Carbon::parse($mov->date)->month;

            $pxA = $mov->prix_achat ?? ($mov->produit->stock->prix_achat ?? 0);
            $pxV = $mov->prix_vente ?? ($mov->produit->stock->prix_vente ?? 0);
            
            if ($mov->type === 'retrait') {
                $statsMensuelles[$m]['ca'] += ($mov->quantite * $pxV);
                $statsMensuelles[$m]['qtv'] += $mov->quantite;
                $statsMensuelles[$m]['cout_achat'] += ($mov->quantite * $pxA);
            } else {
                // It's a return (ajout linked to vente_id)
                $statsMensuelles[$m]['ca'] -= ($mov->quantite * $pxV);
                $statsMensuelles[$m]['qtv'] -= $mov->quantite;
                $statsMensuelles[$m]['cout_achat'] -= ($mov->quantite * $pxA);
            }
        }

        // Calculate global remises per month from the Vente table for consistency
        $ventesAvecRemise = Vente::where('boutique_id', $boutique_id)
            ->whereYear('date_vente', $currentYear)
            ->where('remise', '>', 0)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->get();

        foreach ($ventesAvecRemise as $v) {
            $m = \Carbon\Carbon::parse($v->date_vente)->month;
            $statsMensuelles[$m]['ca'] -= (float)$v->remise;
        }

        // Convert to numerical array and sort desc by month
        $parMois = array_values($statsMensuelles);
        usort($parMois, function($a, $b) {
            return $b['mois_num'] - $a['mois_num'];
        });

        // 3. Global Stats for the current year
        $globalStats = [
            'total_ca' => 0,
            'total_qtv' => 0,
            'total_benefice' => 0
        ];

        foreach ($parMois as $m) {
            $globalStats['total_ca'] += $m['ca'];
            $globalStats['total_qtv'] += $m['qtv'];
            $globalStats['total_benefice'] += ($m['ca'] - $m['cout_achat']);
        }

        return response()->json([
            'par_annee' => $parAnnee,
            'par_mois' => $parMois,
            'annual_stats' => $globalStats,
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

        // Use Inventaire to calculate net stats for the specific year
        // We filter out inventory entries linked to cancelled sales
        $mouvements = Inventaire::with(['produit.stock', 'vente'])
            ->where('boutique_id', $boutique_id)
            ->whereYear('date', $annee)
            ->whereNotNull('vente_id')
            ->whereHas('vente', function($q) {
                $q->whereIn('statut', ['validee', 'payee', 'credit']);
            })
            ->get();

        $statsMensuelles = [];
        for ($i = 1; $i <= 12; $i++) {
            $statsMensuelles[$i] = [
                'mois_num' => $i,
                'ca' => 0,
                'qtv' => 0,
                'cout_achat' => 0,
                'remises_traitees' => []
            ];
        }

        foreach ($mouvements as $mov) {
            $m = (int)Carbon::parse($mov->date)->month;
            $pxA = $mov->prix_achat ?? ($mov->produit->stock->prix_achat ?? 0);
            $pxV = $mov->prix_vente ?? ($mov->produit->stock->prix_vente ?? 0);
            
            if ($mov->type === 'retrait') {
                $statsMensuelles[$m]['ca'] += ($mov->quantite * $pxV);
                $statsMensuelles[$m]['qtv'] += $mov->quantite;
                $statsMensuelles[$m]['cout_achat'] += ($mov->quantite * $pxA);
            } else {
                // It's a return (ajout linked to vente_id)
                $statsMensuelles[$m]['ca'] -= ($mov->quantite * $pxV);
                $statsMensuelles[$m]['qtv'] -= $mov->quantite;
                $statsMensuelles[$m]['cout_achat'] -= ($mov->quantite * $pxA);
            }
        }

        $ventesAvecRemise = Vente::where('boutique_id', $boutique_id)
            ->whereYear('date_vente', $annee)
            ->where('remise', '>', 0)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->get();

        foreach ($ventesAvecRemise as $v) {
            $m = (int)Carbon::parse($v->date_vente)->month;
            $statsMensuelles[$m]['ca'] -= (float)$v->remise;
        }

        $venteList = array_values($statsMensuelles);
        usort($venteList, function($a, $b) {
            return $b['mois_num'] - $a['mois_num'];
        });

        // Calculate global stats for this year
        $annualStats = [
            'total_ca' => 0,
            'total_qtv' => 0,
            'total_benefice' => 0
        ];

        foreach ($venteList as $m) {
            $annualStats['total_ca'] += $m['ca'];
            $annualStats['total_qtv'] += $m['qtv'];
            $annualStats['total_benefice'] += ($m['ca'] - $m['cout_achat']);
        }

        return response()->json([
            'par_mois' => $venteList,
            'annual_stats' => $annualStats
        ], 200);
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
                $vente->remise = $request->remise ?? 0;
                $vente->statut = ($vente->type_paiement === 'credit') ? 'credit' : 'payee';
                $vente->montant_restant = ($vente->type_paiement === 'credit')
                    ? ($request->montant_total - $vente->montant_avance)
                    : 0;
            }

            $vente->date_vente = $request->date ?? now()->format('Y-m-d');
            $vente->save();

            $sumLineRemises = 0;

            foreach ($request->produits as $item) {
                $pId = $item['produits']['id'] ?? 0;

                // if (!$pId) {
                //     throw new \Exception("ID produit manquant dans la requête");
                // }

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

                // Custom prices and discounts
                $prixU = $item['prix_vendu'] ?? ($item['prix'] ?? ($item['produits']['stock']['prix_vente'] ?? ($stock->prix_vente ?? 0)));
                $remiseLine = ($item['remise_unitaire'] ?? 0) * $item['quantite'];
                $sumLineRemises += $remiseLine;

                $detail->prix_unitaire = $prixU;
                $detail->montant = $prixU * $item['quantite'];
                $detail->remise = $remiseLine;
                $detail->montant_total = $detail->montant;
                $detail->montant_paye = $detail->montant - $remiseLine;
                $detail->quantite_restante = $item['quantite'];
                $detail->save();

                if (!$is_proforma) {
                    Inventaire::create([
                        'produit_id' => $pId,
                        'boutique_id' => $boutique_id,
                        'user_id' => $user->id,
                        'vente_id' => $vente->id,
                        'quantite' => $item['quantite'],
                        'type' => 'retrait',
                        'prix_achat' => $stock ? $stock->prix_achat : 0,
                        'prix_vente' => $prixU,
                        'remise' => $remiseLine,
                        'description' => $vente->statut == 'credit' ? "Produit " . $pNom . " vendu a credit " : "Vente du produit # " . (isset($item['produits']['reference']) ? $item['produits']['reference'] : ($item['reference'] ?? "Produit #$pId")),
                        'date' => $vente->date_vente
                    ]);
                }
            }

            // Create virtual Inventaire record for the extra global reduction
            if (!$is_proforma) {
                $extraReduction = (float)$vente->remise - $sumLineRemises;
                if ($extraReduction != 0) {
                    Inventaire::create([
                        'produit_id' => null,
                        'boutique_id' => $boutique_id,
                        'user_id' => $user->id,
                        'vente_id' => $vente->id,
                        'quantite' => 0,
                        'type' => 'retrait',
                        'prix_achat' => 0,
                        'prix_vente' => 0,
                        'remise' => $extraReduction,
                        'description' => 'Remise Globale Additionnelle (Vente #' . $vente->id . ')',
                        'date' => $vente->date_vente
                    ]);
                }
            }

            if (!$is_proforma) {
                $facture = Facture::create([
                    'client_id' => $client_id,
                    'boutique_id' => $boutique_id,
                    'montant_total' => $vente->montant_total,
                    'date_facturation' => $vente->date_vente,
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

                $lineRemise = (float)($detail->remise ?? 0);

                // Log Inventory
                Inventaire::create([
                    'produit_id' => $detail->produit_id,
                    'boutique_id' => $boutique_id,
                    'user_id' => $user->id,
                    'vente_id' => $vente->id,
                    'quantite' => $detail->quantite,
                    'type' => 'retrait',
                    'prix_achat' => $stock ? $stock->prix_achat : 0,
                    'prix_vente' => $detail->prix_unitaire,
                    'remise' => $lineRemise,
                    'description' => 'Conversion Pro-forma #' . $vente->id . ' vers Vente',
                    'date' => now()->format('Y-m-d')
                ]);
            }

            // Virtual record for extra reduction in conversion too
            $totalLineRemises = $vente->detailVentes->sum('remise');
            $venteRemise = (float)$request->input('remise', $totalLineRemises);
            $extraReduction = $venteRemise - $totalLineRemises;
            
            if ($extraReduction != 0) {
                Inventaire::create([
                    'produit_id' => null,
                    'boutique_id' => $boutique_id,
                    'user_id' => $user->id,
                    'vente_id' => $vente->id,
                    'quantite' => 0,
                    'type' => 'retrait',
                    'prix_achat' => 0,
                    'prix_vente' => 0,
                    'remise' => $extraReduction,
                    'description' => 'Remise Globale (Conversion pro-forma #' . $vente->id . ')',
                    'date' => now()->format('Y-m-d')
                ]);
            }

            // 2. Update Vente Identity
            $vente->type_paiement = $type_paiement;
            $vente->remise = $vente->detailVentes->sum('remise');
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
