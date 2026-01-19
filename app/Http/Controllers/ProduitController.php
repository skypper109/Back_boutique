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
                $query->where('quantite', '>', 0)->where('boutique_id', $boutique_id);
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

    // Pour la methode qui nous recupere l'inventaires de l'ensemble des produits qui sont dans la table inventaire :
    public function inventaire()
    {
        // DB::statement("SET lc_time_names = 'fr_FR'");
        $boutique_id = $this->getBoutiqueId();
        $inventaires = Inventaire::with('produit.stock', 'user')
            ->where('boutique_id', $boutique_id)
            ->orderBy('id', 'desc')->get();
        return response()->json($inventaires, 200);
    }

    // Pour la methode qui nous recupere l'inventaire des produits dans un intervalle de date :
    public function inventaireDate($dateDebut, $dateFin)
    {
        // DB::statement("SET lc_time_names = 'fr_FR'");

        $boutique_id = $this->getBoutiqueId();
        $inventaires = Inventaire::with('produit.stock', 'user')
            ->where('boutique_id', $boutique_id)
            ->whereBetween('date', [$dateDebut, $dateFin])
            ->orderBy('created_at', 'desc')->get();
        return response()->json($inventaires, 200);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $boutique_id = $this->getBoutiqueId();
        if (!$boutique_id) {
            return response()->json(['error' => 'Aucune boutique sélectionnée'], 400);
        }

        //Pour la creation d'un nouveau produit:
        $request->validate([
            'nom' => 'required|string|max:255',
            'categorie_id' => 'required|integer',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'quantite' => 'required|integer',
            'prixVente' => 'required|numeric',
            'prixAchat' => 'required|numeric',
        ]);
        $chemin = null;
        if ($request->hasFile('image')) {
            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $chemin = $request->file('image')->storeAs('produit', $fileName, 'public');
            $produit = Produit::create([
                'nom' => $request->input('nom'),
                'description' => $request->input('description'),
                'categorie_id' => $request->input('categorie_id'),
                'image' => asset('storage/' . $chemin)
            ]);
        } else {
            //pour les produits sans fichier image :
            $produit = Produit::create([
                'nom' => $request->input('nom'),
                'description' => $request->input('description'),
                'categorie_id' => $request->input('categorie_id'),
                'image' => asset('storage/produit/default.png')
            ]);
        }

        $produitId = $produit->id;
        $user = Auth::user();
        $boutique_id = $this->getBoutiqueId();

        // L'enregistrement dans la table Inventaire :

        Inventaire::create([
            'produit_id' => $produitId,
            'boutique_id' => $boutique_id,
            'user_id' => $user->id,
            'quantite' => $request->input('quantite'),
            'type' => 'ajout',
            'description' => "Initialisation du produit " . $request->input('nom'),
            'date' => now()->format('Y-m-d')
        ]);

        // Pour l'enregistrement dans la table stock :
        Stock::create([
            'produit_id' => $produitId,
            'boutique_id' => $boutique_id,
            'quantite' => $request->input('quantite'),
            'date_reapprovisionnement' => now()->format('Y-m-d'),
            'prix_achat' => $request->input('prixAchat'),
            'prix_vente' => $request->input('prixVente'),
            'description' => $request->input('description')
        ]);

        // return redirect()->route('produits.index')->with('success','Produit ajouté avec succès');
        return response()->json($produit, 201);
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produit $produit)
    {
        //Pour la modification d'un produit (Nom, Description, Categorie)
        $request->validate([
            'nom' => 'string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'description' => 'nullable|string',
            'categorie_id' => 'integer',
            // Optionnel : Mise à jour du stock
            'prix_vente' => 'nullable|numeric',
            'prix_achat' => 'nullable|numeric',
            'quantite' => 'nullable|integer'
        ]);

        $data = [
            'nom' => $request->input('nom', $produit->nom),
            'description' => $request->input('description', $produit->description),
            'categorie_id' => $request->input('categorie_id', $produit->categorie_id),
        ];

        if ($request->hasFile('image')) {
            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $chemin = $request->file('image')->storeAs('produit', $fileName, 'public');
            $data['image'] = asset('storage/' . $chemin);
        }

        $produit->update($data);

        // Mise à jour du stock pour la boutique actuelle
        $boutique_id = $this->getBoutiqueId();
        if ($request->has('prix_vente') || $request->has('prix_achat') || $request->has('quantite')) {
            $stock = Stock::where('produit_id', $produit->id)->where('boutique_id', $boutique_id)->first();
            if ($stock) {
                $stock->update([
                    'prix_vente' => $request->input('prix_vente', $stock->prix_vente),
                    'prix_achat' => $request->input('prix_achat', $stock->prix_achat),
                    'quantite' => $request->input('quantite', $stock->quantite),
                ]);
            } else {
                Stock::create([
                    'produit_id' => $produit->id,
                    'boutique_id' => $boutique_id,
                    'quantite' => $request->input('quantite', 0),
                    'prix_achat' => $request->input('prix_achat', 0),
                    'prix_vente' => $request->input('prix_vente', 0),
                ]);
            }
        }

        return response()->json(['message' => 'Produit modifié avec succès', 'produit' => $produit], 200);
    }

    public function summary()
    {
        $boutique_id = $this->getBoutiqueId();

        $produit_count = Stock::where('boutique_id', $boutique_id)->where('quantite', '>', 0)->count();
        $total_stock = Stock::where('boutique_id', $boutique_id)
            ->selectRaw('SUM(quantite * prix_vente) as total')
            ->first()->total ?? 0;

        $ventes_jour = DB::table('ventes')
            ->where('boutique_id', $boutique_id)
            ->where('statut', 'validee')
            ->whereDate('date_vente', Carbon::today())
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

    public function importCSV(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'categorie_id' => 'required'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Read headers and remove BOM if present
        $headersString = fgets($handle);
        $headersString = str_replace("\xEF\xBB\xBF", '', $headersString);
        $headers = str_getcsv(trim($headersString), ';');

        // Clean headers (trim spaces)
        $headers = array_map('trim', $headers);

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
                'categorie_id' => $request->input('categorie_id'),
                'description' => $data['description'] ?? '',
                'image' => asset('storage/produit/default.png')
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
                'description' => "Importation CSV",
                'date' => now()->format('Y-m-d')
            ]);

            $count++;
        }

        fclose($handle);

        return response()->json(['message' => "$count produits importés avec succès"], 201);
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

        if ($request->has('quantite')) {
            $stock->update([
                'quantite' => $request->quantite,
                'date_reapprovisionnement' => now()->format('Y-m-d')
            ]);
        }

        // Log::info("Product {$id} restored by user " . auth()->id() . " for boutique {$boutique_id}");

        return response()->json(['message' => 'Produit restauré avec succès'], 200);
    }
}
