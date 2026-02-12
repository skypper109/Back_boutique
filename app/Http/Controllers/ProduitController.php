<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Produit;
use App\Models\Inventaire;
use App\Models\inventaires;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // la liste des produits avec a la fois les categories et les stocks lorsque la quantite est superieur a 0:

        $boutique_id = $this->getBoutiqueId();
        $produits = Produit::with(['categorie', 'stock' => function ($query) use ($boutique_id) {
            $query->where('boutique_id', $boutique_id);
        }])
            ->whereHas('stock', function ($query) use ($boutique_id) {
                $query->where('boutique_id', $boutique_id);
            })
            ->orderBy('id', 'desc')->get();
        return response()->json($produits, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function reapproIndex()
    {
        // Pour la liste des produits a reapprovisionner:
        $boutique_id = $this->getBoutiqueId();
        $produits = Produit::with(['categorie', 'stock' => function ($query) use ($boutique_id) {
            $query->where('boutique_id', $boutique_id);
        }])
            ->whereHas('stock', function ($query) use ($boutique_id) {
                $query->where('boutique_id', $boutique_id);
            })->get();

        // Sort by quantity manually if needed or via query
        $produits = $produits->sortBy(fn($p) => $p->stock ? $p->stock->quantite : 0)->values();

        return response()->json($produits, 200);
    }

    public function reapproCreate(Request $request)
    {
        // Pour le reaprovisionnement des produits:
        $request->validate([
            'produits' => 'Array'
        ]);

        $user = Auth::user();
        $boutique_id = $this->getBoutiqueId();

        foreach ($request->produits as $produit) {
            $produitId = $produit['produit_id'];
            $quantite = $produit['quantite'];
            $prix_achat = $produit['prix_achat'];
            $prix_vente = $produit['prix_vente'];
            $prod = $produit['produit'];
            // $description = $produit['description'];

            // L'enregistrement dans la table Inventaire :

            Inventaire::create([
                'produit_id' => $produitId,
                'boutique_id' => $boutique_id,
                'user_id' => $user->id,
                'quantite' => $quantite,
                'type' => 'ajout',
                'prix_achat' => $prix_achat,
                'prix_vente' => $prix_vente,
                'remise' => 0,
                'description' => 'Reapprovisionnement du produit ' . $prod,
                'date' => now()->format('Y-m-d')
            ]);

            // Pour la mise a jour dans la table stock :
            $stock = Stock::where('produit_id', $produitId)->where('boutique_id', $boutique_id)->first();
            if ($stock) {
                $stock->update([
                    'quantite' => $stock->quantite + $quantite,
                    'date_reapprovisionnement' => now()->format('Y-m-d'),
                    'prix_achat' => $prix_achat,
                    'prix_vente' => $prix_vente,
                    'description' => 'Reapprovisionnement de ' . $quantite . ' produits'
                ]);
            } else {
                Stock::create([
                    'produit_id' => $produitId,
                    'boutique_id' => $boutique_id,
                    'quantite' => $quantite,
                    'date_reapprovisionnement' => now()->format('Y-m-d'),
                    'prix_achat' => $prix_achat,
                    'prix_vente' => $prix_vente,
                    'description' => 'Initialisation stock via réappro'
                ]);
            }
        }
        return response()->json(['message' => 'Produit reapprovisionné avec succès'], 201);
    }

    public function rupture()
    {
        $boutique_id = $this->getBoutiqueId();
        $produits = Produit::with(['categorie', 'stock' => function ($query) use ($boutique_id) {
            $query->where('boutique_id', $boutique_id);
        }])
            ->whereHas('stock', function ($query) use ($boutique_id) {
                $query->where('quantite', '<=', 0)->where('boutique_id', $boutique_id);
            })
            ->orderBy('id', 'desc')->get();
        return response()->json($produits, 200);
    }
    public function stock()
    {
        $boutique_id = $this->getBoutiqueId();
        $produits = Produit::with(['categorie', 'stock' => function ($query) use ($boutique_id) {
            $query->where('boutique_id', $boutique_id);
        }])
            ->whereHas('stock', function ($query) use ($boutique_id) {
                $query->where('quantite', '>', 0)->where('boutique_id', $boutique_id);
            })
            ->orderBy('id', 'desc')->get();
        return response()->json($produits, 200);
    }

    // Pour la methode qui nous recupere l'inventaire de l'ensemble des produits
    public function inventaire()
    {
        $boutique_id = $this->getBoutiqueId();
        $inventaires = Inventaire::with('produit.stock', 'user', 'boutique')
            ->whereHas('produit')
            ->where('boutique_id', $boutique_id)
            ->orderBy('created_at', 'desc')->get();

        $payments = \App\Models\PaiementCredit::with(['vente.client', 'user'])
            ->where('boutique_id', $boutique_id)
            ->orderBy('date_paiement', 'desc')
            ->get();

        return $this->formatInventaireResponse($inventaires, $payments);
    }

    // Pour la methode qui nous recupere l'inventaire dans un intervalle de date
    public function inventaireDate($dateDebut, $dateFin)
    {
        $boutique_id = $this->getBoutiqueId();
        $inventaires = Inventaire::with('produit.stock', 'user', 'boutique')
            ->where('boutique_id', $boutique_id)
            ->whereBetween('date', [$dateDebut, $dateFin])
            ->orderBy('created_at', 'desc')->get();

        $payments = \App\Models\PaiementCredit::with(['vente.client', 'user'])
            ->where('boutique_id', $boutique_id)
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->orderBy('date_paiement', 'desc')
            ->get();

        return $this->formatInventaireResponse($inventaires, $payments, $dateDebut, $dateFin);
    }

    private function formatInventaireResponse($inventaires, $payments, $dateDebut = null, $dateFin = null)
    {
        $stats = [
            'totalEntrees' => 0,
            'totalSorties' => 0,
            'valeurAchatEntrante' => 0,
            'valeurVenteSortante' => 0,
            'recettesCredit' => 0,
            'beneficeTheorique' => 0,
            'netMouvement' => 0
        ];

        // Process payments
        $formattedPayments = $payments->map(function($p) use (&$stats) {
            $stats['recettesCredit'] += (float)$p->montant;
            return array_merge($p->toArray(), [
                'type' => 'paiement',
                'date' => $p->date_paiement,
                'isGroup' => false,
                'impactNet' => (float)$p->montant,
                'description' => "Reglement Credit #{$p->vente_id} (" . ($p->vente->client->nom ?? 'Client') . ")"
            ]);
        })->toArray();

        // Group Inventaire
        $groupedGroups = [];
        $transactionMap = [];

        foreach ($inventaires as $item) {
            $groupKey = ($item->vente_id && $item->type !== 'paiement') ? "{$item->vente_id}_{$item->type}" : null;
            
            if ($groupKey) {
                if (!isset($transactionMap[$groupKey])) {
                    $isReturn = $item->type === 'ajout';
                    $transactionMap[$groupKey] = [
                        'isGroup' => true,
                        'vente_id' => $item->vente_id,
                        'date' => $item->date,
                        'created_at' => $item->created_at,
                        'type' => $item->type,
                        'isReturn' => $isReturn,
                        'description' => $item->description,
                        'user' => $item->user,
                        'items' => [],
                        'totalQuantite' => 0,
                        'totalRemise' => $isReturn ? 0 : (float)($item->remise ?? 0),
                        'impactNet' => 0,
                        'showDetails' => false
                    ];
                }
                
                $transactionMap[$groupKey]['items'][] = $item;
                $transactionMap[$groupKey]['totalQuantite'] += $item->quantite;
                
                $pxAchat = $item->prix_achat ?? ($item->produit->stock->prix_achat ?? 0);
                $pxVente = $item->prix_vente ?? ($item->produit->stock->prix_vente ?? 0);
                $transactionMap[$groupKey]['impactNet'] += ($item->quantite * ($pxVente - $pxAchat));
            } else {
                $itemArray = $item->toArray();
                $pxAchat = $item->prix_achat ?? ($item->produit->stock->prix_achat ?? 0);
                $pxVente = $item->prix_vente ?? ($item->produit->stock->prix_vente ?? 0);
                $remise = (float)($item->remise ?? 0);
                
                if ($item->type === 'retrait') {
                    $impact = (($item->quantite * $pxVente) - $remise) - ($item->quantite * $pxAchat);
                } else {
                    $impact = ($item->quantite * $pxAchat);
                }
                
                $groupedGroups[] = array_merge($itemArray, [
                    'isGroup' => false,
                    'impactNet' => $impact
                ]);
            }
        }

        // Finalize groups and update global stats
        foreach ($transactionMap as $key => &$g) {
            if ($g['isReturn']) {
                $g['description'] = count($g['items']) > 1 ? "Retour Groupé #{$g['vente_id']} (" . count($g['items']) . " articles)" : $g['items'][0]->description;
                
                // Deduction from global stats for returns
                $stats['totalSorties'] -= $g['totalQuantite'];
                $valVente = 0;
                foreach($g['items'] as $it) {
                    $pxV = $it->prix_vente ?? ($it->produit->stock->prix_vente ?? 0);
                    $valVente += ($it->quantite * $pxV);
                }
                $stats['valeurVenteSortante'] -= $valVente;
                $stats['beneficeTheorique'] -= $g['impactNet'];
            } else {
                $g['impactNet'] -= $g['totalRemise'];
                if (count($g['items']) > 1) {
                    $g['description'] = "Vente Groupée #{$g['vente_id']} (" . count($g['items']) . " articles)";
                }
                
                // Addition to global stats for sales (retrait)
                if ($g['type'] === 'retrait') {
                    $stats['totalSorties'] += $g['totalQuantite'];
                    $valVente = 0;
                    foreach($g['items'] as $it) {
                        $pxV = $it->prix_vente ?? ($it->produit->stock->prix_vente ?? 0);
                        $valVente += ($it->quantite * $pxV);
                    }
                    $stats['valeurVenteSortante'] += ($valVente - $g['totalRemise']);
                    $stats['beneficeTheorique'] += $g['impactNet'];
                } else {
                    // Purchase entry
                    $stats['totalEntrees'] += $g['totalQuantite'];
                    $valAchat = 0;
                    foreach($g['items'] as $it) {
                        $pxA = $it->prix_achat ?? ($it->produit->stock->prix_achat ?? 0);
                        $valAchat += ($it->quantite * $pxA);
                    }
                    $stats['valeurAchatEntrante'] += $valAchat;
                }
            }
            $groupedGroups[] = $g;
        }

        // Finalize individual item stats
        foreach ($groupedGroups as $item) {
            if (isset($item['isGroup']) && $item['isGroup']) continue;
            if ($item['type'] === 'paiement') continue;

            $pxAchat = $item['prix_achat'] ?? ($item['produit']['stock']['prix_achat'] ?? 0);
            $pxVente = $item['prix_vente'] ?? ($item['produit']['stock']['prix_vente'] ?? 0);
            $remise = (float)($item['remise'] ?? 0);
            $qte = (float)$item['quantite'];

            if ($item['type'] === 'retrait') {
                $stats['totalSorties'] += $qte;
                $isCreditSale = stripos($item['description'] ?? '', 'credit') !== false;
                if (!$isCreditSale) {
                    $stats['valeurVenteSortante'] += (($qte * $pxVente) - $remise);
                }
                $stats['beneficeTheorique'] += $item['impactNet'];
            } else {
                if (isset($item['vente_id']) && $item['vente_id']) {
                    $stats['totalSorties'] -= $qte;
                    $stats['valeurVenteSortante'] -= ($qte * $pxVente);
                    $stats['beneficeTheorique'] -= (($qte * $pxVente) - ($qte * $pxAchat));
                } else {
                    $stats['totalEntrees'] += $qte;
                    $stats['valeurAchatEntrante'] += ($qte * $pxAchat);
                }
            }
        }

        $stats['netMouvement'] = $stats['totalEntrees'] - $stats['totalSorties'];

        // Combine and sort finally by date
        $finalList = array_merge($groupedGroups, $formattedPayments);
        usort($finalList, function($a, $b) {
            $dateA = $a['date'] ?? $a['created_at'];
            $dateB = $b['date'] ?? $b['created_at'];
            return strcmp($dateB, $dateA);
        });

        return response()->json([
            'items' => $finalList,
            'stats' => $stats,
            'boutique' => count($inventaires) > 0 ? $inventaires[0]->boutique : null
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Produit $produit)
    {
        //Pour afficher un produit specifique:
        return response()->json($produit, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produit $produit)
    {
        //la vue pour la modification d'un produit avec les categories, inventaires et les stocks:
        // $produit = Produit::with([
        //     'categorie'=> function ($query){
        //         $query->select('id','nom');
        //     },
        //     'stock' => function ($query){
        //         $query->select('prix_achat','prix_vente','quantite');
        //     }
        // ])->find($produit->id);
        $boutique_id = $this->getBoutiqueId();
        $produitA = Produit::produitStock($produit->id, $boutique_id)->first();

        return response()->json($produitA, 200);
    }
    public function editProd($id)
    {
        // Pour recuperer un produit specifique avec les categories, les stocks et les inventaires:

        $boutique_id = $this->getBoutiqueId();
        $produit = Produit::with(['categorie', 'stock' => function ($q) use ($boutique_id) {
            $q->where('boutique_id', $boutique_id);
        }, 'inventaires' => function ($q) use ($boutique_id) {
            $q->where('boutique_id', $boutique_id);
        }])->find($id);
        return response()->json($produit, 200);
    }

    public function store(Request $request)
    {
        $boutique_id = $this->getBoutiqueId();
        if (!$boutique_id) {
            return response()->json(['error' => 'Aucune boutique sélectionnée'], 400);
        }

        // Pour la creation d'un nouveau produit:
        $fields = $request->validate([
            'nom' => 'required|string',
            'description' => 'nullable|string',
            'categorie_id' => 'required|exists:categories,id',
            'prix_achat' => 'required|numeric|min:0', 
            'prix_vente' => 'required|numeric|min:0',
            'prix_detail' => 'nullable|numeric|min:0',
            'prix_master' => 'nullable|numeric|min:0',
            'quantite' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'reference' => 'nullable|string',
            'date_achat' => 'nullable|date',
            'date_fin_garantie' => 'nullable|date',
        ]);

        $chemin = null;
        if ($request->hasFile('image')) {
            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $chemin = $request->file('image')->storeAs('produit', $fileName, 'public');
            $imagePath = asset('storage/' . $chemin);
        } else {
            $imagePath = asset('storage/produit/default.png');
        }

        $produit = Produit::create([
            'nom' => $request->input('nom'),
            'reference' => $request->input('reference'),
            'description' => $request->input('description'),
            'categorie_id' => $request->input('categorie_id'),
            'image' => $imagePath,
            'prix_master' => $request->input('prix_master'),
            'prix_detail' => $request->input('prix_detail'),
        ]);

        $produitId = $produit->id;
        $user = Auth::user();

        // L'enregistrement dans la table Inventaire :
        Inventaire::create([
            'produit_id' => $produitId,
            'boutique_id' => $boutique_id,
            'user_id' => $user->id,
            'quantite' => $request->input('quantite'),
            'type' => 'ajout',
            'prix_achat' => $request->input('prix_achat'),
            'prix_vente' => $request->input('prix_vente'),
            'remise' => 0,
            'description' => "Initialisation du produit " . $request->input('nom'),
            'date' => now()->format('Y-m-d')
        ]);

        // Pour l'enregistrement dans la table stock :
        Stock::create([
            'produit_id' => $produitId,
            'boutique_id' => $boutique_id,
            'quantite' => $request->input('quantite'),
            'date_reapprovisionnement' => $request->input('date_achat') ?? now()->format('Y-m-d'),
            'prix_achat' => $request->input('prix_achat'),
            'prix_vente' => $request->input('prix_vente'),
            'description' => $request->input('description')
        ]);

        return response()->json($produit, 201);
    }

    public function update(Request $request, Produit $produit)
    {
        // Pour la modification d'un produit (Nom, Description, Categorie)
        $request->validate([
            'nom' => 'string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'description' => 'nullable|string',
            'categorie_id' => 'integer',
            'reference' => 'nullable|string|max:50',
            'prix_vente' => 'nullable|numeric',
            'prix_achat' => 'nullable|numeric',
            'prix_detail' => 'nullable|numeric',
            'prix_master' => 'nullable|numeric',
            'quantite' => 'nullable|integer',
            // Accepter aussi camelCase au cas où
            'prixVente' => 'nullable|numeric',
            'prixAchat' => 'nullable|numeric',
        ]);

        $data = [
            'nom' => $request->input('nom', $produit->nom),
            'reference' => $request->input('reference', $produit->reference),
            'description' => $request->input('description', $produit->description),
            'categorie_id' => $request->input('categorie_id', $produit->categorie_id),
            'prix_detail' => $request->input('prix_detail', $produit->prix_detail),
            'prix_master' => $request->input('prix_master', $produit->prix_master),
        ];

        if ($request->hasFile('image')) {
            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $chemin = $request->file('image')->storeAs('produit', $fileName, 'public');
            $data['image'] = asset('storage/' . $chemin);
        }

        $produit->update($data);

        // Mise à jour du stock pour la boutique actuelle
        $boutique_id = $this->getBoutiqueId();
        
        // Handle snake_case or camelCase inputs
        $prix_vente = $request->input('prix_vente') ?? $request->input('prixVente');
        $prix_achat = $request->input('prix_achat') ?? $request->input('prixAchat');
        $quantite = $request->input('quantite');

        // Only update stock if relevant fields are present
        if ($prix_vente !== null || $prix_achat !== null || $quantite !== null) {
            $stock = Stock::where('produit_id', $produit->id)->where('boutique_id', $boutique_id)->first();
            
            if ($stock) {
                $stock->update([
                    'prix_vente' => $prix_vente ?? $stock->prix_vente,
                    'prix_achat' => $prix_achat ?? $stock->prix_achat,
                    'quantite' => $quantite ?? $stock->quantite,
                ]);
            } else {
                Stock::create([
                    'produit_id' => $produit->id,
                    'boutique_id' => $boutique_id,
                    'quantite' => $quantite ?? 0,
                    'prix_achat' => $prix_achat ?? 0,
                    'prix_vente' => $prix_vente ?? 0,
                    'date_reapprovisionnement' => now()->format('Y-m-d')
                ]);
            }
        }

        return response()->json(['message' => 'Produit modifié avec succès', 'produit' => $produit], 200);
    }

    public function importCSV(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'categorie_id' => 'required'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Expected header as per user request
        $expectedHeader = ['nom', 'reference', 'prix_achat', 'prix_vente', 'prix_master', 'quantite', 'description'];

        // Read headers and remove BOM if present
        $headersString = fgets($handle);
        $headersString = str_replace("\xEF\xBB\xBF", '', $headersString);
        $headers = str_getcsv(trim($headersString), ';');

        // Clean headers (trim spaces)
        $headers = array_map('trim', $headers);

        // Strict header validation
        if ($headers !== $expectedHeader) {
            fclose($handle);
            return response()->json([
                'message' => "L'entête du fichier est incorrecte.",
                'error' => "Format attendu: " . implode(';', $expectedHeader),
                'received' => implode(';', $headers)
            ], 422);
        }

        $user = Auth::user();
        $boutique_id = $this->getBoutiqueId();
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) continue;

            // Ensure row matches headers count
            if (count($headers) !== count($row)) {
                continue;
            }
            $data = array_combine($headers, $row);

            $produit = Produit::create([
                'nom' => $data['nom'] ?? 'Produit sans nom',
                'reference' => $data['reference'] ?? null,
                'categorie_id' => $request->input('categorie_id'),
                'description' => $data['description'] ?? '',
                'image' => asset('storage/produit/default.png'),
                'prix_detail' => $data['prix_detail'] ?? $data['prix_vente'] ?? 0,
                'prix_master' => $data['prix_master'] ?? null
            ]);

            Stock::create([
                'produit_id' => $produit->id,
                'boutique_id' => $boutique_id,
                'quantite' => $data['quantite'] ?? 0,
                'prix_achat' => $data['prix_achat'] ?? 0,
                'prix_vente' => $data['prix_vente'] ?? 0,
                'date_reapprovisionnement' => now()->format('Y-m-d')
            ]);

            Inventaire::create([
                'produit_id' => $produit->id,
                'boutique_id' => $boutique_id,
                'user_id' => $user->id,
                'quantite' => $data['quantite'] ?? 0,
                'type' => 'ajout',
                'prix_achat' => $data['prix_achat'] ?? 0,
                'prix_vente' => $data['prix_vente'] ?? 0,
                'remise' => 0,
                'description' => "Importation CSV",
                'date' => now()->format('Y-m-d')
            ]);

            $count++;
        }

        fclose($handle);

        if ($count === 0) {
            return response()->json(['message' => "Aucun produit n'a pu être importé. Vérifiez le contenu du fichier."], 400);
        }

        return response()->json(['message' => "$count produits importés avec succès"], 201);
    }

    public function summary()
    {
        $boutique_id = $this->getBoutiqueId();

        $produit_count = Stock::where('boutique_id', $boutique_id)
            ->whereHas('produit')
            ->where('quantite', '>', 0)
            ->count();
        $total_stock = Stock::where('boutique_id', $boutique_id)
            ->whereHas('produit')
            ->selectRaw('SUM(quantite * prix_vente) as total')
            ->first()->total ?? 0;

        $ventes_jour = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->whereIn('statut', ['validee', 'payee', 'credit'])
            ->whereRaw("date_vente = ?", [Carbon::today()->format('Y-m-d')])
            ->count();

        $categorie_count = \App\Models\Categorie::where('boutique_id', $boutique_id)
            ->orWhereNull('boutique_id')
            ->count();

        return response()->json([
            'produit_count' => $produit_count,
            'total_stock' => (float)$total_stock,
            'ventes_jour' => $ventes_jour,
            'categorie_count' => $categorie_count,
            'annee_active' => now()->year
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produit $produit)
    {
        $boutique_id = $this->getBoutiqueId();
        if (!$boutique_id) {
            return response()->json(['error' => 'Aucune boutique sélectionnée'], 400);
        }

        // Verify product belongs to a stock in this boutique
        $hasStock = \App\Models\Stock::where('produit_id', $produit->id)
            ->where('boutique_id', $boutique_id)
            ->exists();

        if (!$hasStock) {
            return response()->json(['error' => 'Produit non trouvé dans cette boutique'], 404);
        }

        //Pour la suppression d'un produit (Soft Delete enabled):
        $produit->delete();

        // \Log::info("Product {$produit->id} soft deleted by user " . auth()->id() . " for boutique {$boutique_id}");

        return response()->json(['message' => 'Produit déplacé vers la corbeille'], 200);
    }

    public function trashed()
    {
        $boutique_id = $this->getBoutiqueId();
        if (!$boutique_id) {
            return response()->json(['error' => 'Aucune boutique sélectionnée'], 400);
        }

        $produits = Produit::onlyTrashed()
            ->with(['categorie', 'stock' => function ($query) use ($boutique_id) {
                $query->where('boutique_id', $boutique_id);
            }])
            ->whereHas('stock', function ($query) use ($boutique_id) {
                $query->where('boutique_id', $boutique_id);
            })
            ->orderBy('deleted_at', 'desc')->get();

        return response()->json($produits, 200);
    }

    public function restore(Request $request, $id)
    {
        $boutique_id = $this->getBoutiqueId();
        if (!$boutique_id) {
            return response()->json(['error' => 'Aucune boutique sélectionnée'], 400);
        }

        $produit = Produit::onlyTrashed()->findOrFail($id);

        // Verify product belongs to this boutique's stock
        $stock = Stock::where('produit_id', $id)
            ->where('boutique_id', $boutique_id)
            ->first();

        if (!$stock) {
            return response()->json(['error' => 'Produit non trouvé dans cette boutique'], 404);
        }

        $produit->restore();

        if ($request->has('quantite') != 0) {
            $stock->update([
                'quantite' => $request->quantite,
                'date_reapprovisionnement' => now()->format('Y-m-d')
            ]);
        }else{

            $stock->update([
                'quantite' => $produit->quantite,
                'date_reapprovisionnement' => now()->format('Y-m-d')
            ]);
        }

        // Log::info("Product {$id} restored by user " . auth()->id() . " for boutique {$boutique_id}");

        return response()->json(['message' => 'Produit restauré avec succès'], 200);
    }
}
