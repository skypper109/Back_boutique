<?php

namespace App\Http\Controllers;

use App\Models\Annee;
use Illuminate\Http\Request;

class AnneeController extends Controller
{
    public function index()
    {
        $boutique_id = $this->getBoutiqueId();
        $annees = Annee::where(function ($q) use ($boutique_id) {
            $q->whereNull('boutique_id')
                ->orWhere('boutique_id', $boutique_id);
        })->orderBy('annee', 'desc')->get();

        return response()->json($annees, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'annee' => 'required|integer',
        ]);

        $boutique_id = $this->getBoutiqueId();

        $annee = Annee::updateOrCreate(
            ['annee' => $request->annee, 'boutique_id' => $boutique_id],
            ['is_active' => $request->is_active ?? true]
        );

        return response()->json($annee, 201);
    }

    public function toggleStatus($id)
    {
        $annee = Annee::findOrFail($id);
        $annee->is_active = !$annee->is_active;
        $annee->save();

        return response()->json($annee, 200);
    }

    public function destroy($id)
    {
        $annee = Annee::findOrFail($id);
        $annee->delete();

        return response()->json(['message' => 'Année supprimée'], 200);
    }
}
