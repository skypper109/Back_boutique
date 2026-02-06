<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\User;
use App\Models\Vente;
use App\Models\DetailVente;
use Illuminate\Support\Facades\Auth;

class BoutiqueController extends Controller
{
    public function allStats()
    {
        $user = Auth::user();
        $boutiques = Boutique::where('user_id', $user->id)->get();
        $reports = [];

        foreach ($boutiques as $b) {
            $reports[] = [
                'id' => $b->id,
                'nom' => $b->nom,
                'revenue' => \App\Models\Vente::where('boutique_id', $b->id)->whereIn('statut', ['validee', 'payee', 'credit'])->sum('montant_total'),
                'sales_count' => \App\Models\Vente::where('boutique_id', $b->id)->whereIn('statut', ['validee', 'payee', 'credit'])->count(),
                'users_count' => \App\Models\User::where('boutique_id', $b->id)->count(),
                'is_active' => $b->is_active
            ];
        }

        return response()->json($reports, 200);
    }

    public function stats(string $id)
    {
        $boutique = Boutique::findOrFail($id);

        $salesCount = Vente::where('boutique_id', $id)->whereIn('statut', ['validee', 'payee', 'credit'])->count();
        $totalRevenue = Vente::where('boutique_id', $id)->whereIn('statut', ['validee', 'payee', 'credit'])->sum('montant_total');
        $usersCount = User::where('boutique_id', $id)->count();

        $topProducts = DetailVente::with('produit')
            ->whereHas('produit')
            ->whereHas('vente', function ($q) use ($id) {
                $q->where('boutique_id', $id);
            })
            ->selectRaw('produit_id, SUM(quantite) as total_qty, SUM(detail_ventes.montant_total) as total_amount')
            ->groupBy('produit_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return response()->json([
            'boutique' => $boutique,
            'stats' => [
                'sales_count' => $salesCount,
                'total_revenue' => $totalRevenue,
                'users_count' => $usersCount,
                'top_products' => $topProducts
            ]
        ], 200);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        // if ($user->role === 'admin') {
        //     // Un admin voit toutes les boutiques qu'il a créées ou toutes les boutiques s'il est super-admin
        //     // Pour l'instant, on retourne tout s'il est admin
        //     return response()->json(Boutique::all(), 200);
        // }

        // Pour les autres roles, peut-être filtrer ?
        // Mais selon la demande, l'admin doit pouvoir switcher.
        return response()->json(Boutique::where('user_id', $user->id)->get(), 200);
    }

    /**
     * Store a newly created resource in.
     */
    public function store(Request $request)
    {
        try {
            $fields = $request->validate([
                'nom' => 'required|string|unique:boutiques,nom',
                'adresse' => 'nullable|string',
                'telephone' => 'nullable|string',
                'email' => 'nullable|string|email',
                // PDF Customization
                'logo' => 'nullable|string',
                'description_facture' => 'nullable|string',
                'description_bordereau' => 'nullable|string',
                'description_recu' => 'nullable|string',
                'footer_facture' => 'nullable|string',
                'footer_bordereau' => 'nullable|string',
                'footer_recu' => 'nullable|string',
                'couleur_principale' => 'nullable|string',
                'couleur_secondaire' => 'nullable|string',
                'devise' => 'nullable|string',
                'format_facture' => 'nullable|string',
            ]);

            $fields['user_id'] = Auth::id();
            $fields['is_active'] = true;

            \Illuminate\Support\Facades\Log::info('Creating boutique with fields:', $fields);

            $boutique = Boutique::create($fields);

            return response()->json($boutique, 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating boutique: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'message' => 'Erreur lors de la création de la boutique',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeWithManager(Request $request)
    {
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                // 1. Validate Boutique and Manager info
                $request->validate([
                    'boutique.nom' => 'required|string|unique:boutiques,nom',
                    'boutique.adresse' => 'nullable|string',
                    'boutique.telephone' => 'nullable|string',
                    'boutique.email' => 'nullable|string|email',
                    // PDF Customization
                    'boutique.logo' => 'nullable|string',
                    'boutique.description_facture' => 'nullable|string',
                    'boutique.description_bordereau' => 'nullable|string',
                    'boutique.description_recu' => 'nullable|string',
                    'boutique.footer_facture' => 'nullable|string',
                    'boutique.footer_bordereau' => 'nullable|string',
                    'boutique.footer_recu' => 'nullable|string',
                    'boutique.couleur_principale' => 'nullable|string',
                    'boutique.couleur_secondaire' => 'nullable|string',
                    'boutique.devise' => 'nullable|string',
                    'boutique.format_facture' => 'nullable|string',
                    
                    'manager.name' => 'required|string',
                    'manager.email' => 'required|string|email|unique:users,email',
                    'manager.telephone' => 'nullable|string',
                    'manager.password' => 'required|string|min:6',
                ]);

                // 2. Create Boutique
                $boutiqueFields = $request->input('boutique');
                $boutiqueFields['user_id'] = Auth::id(); // The admin who created it
                $boutiqueFields['is_active'] = true;
                $boutique = Boutique::create($boutiqueFields);

                // 3. Create Manager User
                $managerFields = $request->input('manager');
                $user = User::create([
                    'name' => $managerFields['name'],
                    'email' => $managerFields['email'],
                    'password' => bcrypt($managerFields['password']),
                    'role' => 'gestionnaire',
                    'boutique_id' => $boutique->id,
                    'is_active' => true
                ]);

                return response()->json([
                    'message' => 'Boutique and Manager created successfully',
                    'boutique' => $boutique,
                    'manager' => $user
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la boutique et du gestionnaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(Boutique::findOrFail($id), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $boutique = Boutique::findOrFail($id);

        $fields = $request->validate([
            'nom' => 'required|string|unique:boutiques,nom,' . $id,
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|string|email',
            // PDF Customization
            'logo' => 'nullable|string',
            'description_facture' => 'nullable|string',
            'description_bordereau' => 'nullable|string',
            'description_recu' => 'nullable|string',
            'footer_facture' => 'nullable|string',
            'footer_bordereau' => 'nullable|string',
            'footer_recu' => 'nullable|string',
            'couleur_principale' => 'nullable|string',
            'couleur_secondaire' => 'nullable|string',
            'devise' => 'nullable|string',
            'format_facture' => 'nullable|string',
        ]);

        $boutique->update($fields);

        return response()->json($boutique, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $boutique = Boutique::findOrFail($id);
        $boutique->delete();

        return response()->json(['message' => 'Boutique supprimée'], 200);
    }
}
