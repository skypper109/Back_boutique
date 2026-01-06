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
        //Pour la creation d'un nouveau produit:
        $request->validate([
            'nom' => 'required|string|max:255',
            'categorie_id' => 'required|integer',
            'description' => 'nullable|string',
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
        //Pour la modification d'un produit uniquement le nom et la description et l'id:
        $request->validate([
            'nom' => 'string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'description' => 'nullable|string'
        ]);
        if ($request->hasFile('image')) {
            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $chemin = $request->file('image')->storeAs('produit', $fileName, 'public');
            $produit->update([
                'nom' => $request->input('nom'),
                'description' => $request->input('description'),
                'categorie_id' => $request->input('categorie_id'),
                'image' => asset('storage/' . $chemin)
            ]);
            return response()->json(['message' => 'Produit modifié avec succès'], 200);
        } else {
            $produit->update([
                'nom' => $request->input('nom'),
                'description' => $request->input('description'),
                'categorie_id' => $request->input('categorie_id')
            ]);
            return response()->json(['message' => 'Produit modifié avec succès'], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produit $produit)
    {
        //Pour la suppression d'un produit:
        $produit->delete();
        return response()->json(['message' => 'Produit supprimé avec succès'], 200);
    }
}
