<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\User;
use App\Models\Vente;
use App\Models\DetailVente;

class BoutiqueController extends Controller
{
    public function stats(string $id)
    {
        $boutique = Boutique::findOrFail($id);

        $salesCount = Vente::where('boutique_id', $id)->count();
        $totalRevenue = Vente::where('boutique_id', $id)->sum('montant_total');
        $usersCount = User::where('boutique_id', $id)->count();

        $topProducts = DetailVente::with('produit')
            ->whereHas('vente', function ($q) use ($id) {
                $q->where('boutique_id', $id);
            })
            ->selectRaw('produit_id, SUM(quantite) as total_qty, SUM(montant_total) as total_amount')
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
        return response()->json(Boutique::all(), 200);
    }

    /**
     * Store a newly created resource in.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'nom' => 'required|string',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|string|email'
        ]);

        $boutique = Boutique::create($fields);

        return response()->json($boutique, 201);
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
            'nom' => 'required|string',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|string|email'
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
