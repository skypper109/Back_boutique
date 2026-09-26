<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expense::query();
        
        $boutiqueId = $this->getBoutiqueId() ?: $request->boutique_id;
        if ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }

        if ($request->has('month') && $request->month) {
            $query->whereMonth('date', $request->month);
        }
        if ($request->has('year') && $request->year) {
            $query->whereYear('date', $request->year);
        }
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderByDesc('date')->paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $boutiqueId = $request->boutique_id ?: $this->getBoutiqueId();
        if (!$boutiqueId) {
            return response()->json(['message' => 'Boutique introuvable.'], 422);
        }

        $fields = $request->validate([
            'type' => 'required|string',
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'boutique_id' => 'nullable|exists:boutiques,id',
        ]);

        $fields['boutique_id'] = $boutiqueId;
        $fields['user_id'] = Auth::id();

        $expense = Expense::create($fields);

        // Écriture comptable automatique SYSCOHADA (Débit Charges Classe 6, Crédit Caisse)
        app(\App\Services\ComptaService::class)->enregistrerDepense($expense);

        return response()->json($expense, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $fields = $request->validate([
            'type' => 'required|string',
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $comptaService = app(\App\Services\ComptaService::class);
        // Contre-passation de l'ancienne version
        $comptaService->enregistrerAnnulationDepense($expense);
        \App\Models\EcritureComptable::where('source_type', 'Expense')
            ->where('source_id', $expense->id)
            ->delete();

        $expense->update($fields);

        // Enregistrement de l'écriture rectifiée
        $comptaService->enregistrerDepense($expense);

        return response()->json($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        // SYSCOHADA : Contre-passation de la dépense annulée
        app(\App\Services\ComptaService::class)->enregistrerAnnulationDepense($expense);
        
        $expense->delete();
        return response()->json(['message' => 'Dépense supprimée']);
    }

    public function dashboard(Request $request)
    { 
        $boutiqueId = $this->getBoutiqueId() ?: $request->boutique_id;

        $query = Expense::query();
        if ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }

        $year = $request->get('year', date('Y'));
        
        $totalByYear = (clone $query)->whereYear('date', $year)->sum('montant');
        
        $monthlyEvolution = (clone $query)->whereYear('date', $year)
            ->selectRaw('MONTH(date) as month, SUM(montant) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $breakdownByType = (clone $query)->whereYear('date', $year)
            ->selectRaw('type, SUM(montant) as total')
            ->groupBy('type')
            ->get();

        return response()->json([
            'total_year' => (float)($totalByYear ?? 0),
            'monthly_evolution' => $monthlyEvolution ?? [],
            'breakdown_by_type' => $breakdownByType ?? [],
            'year' => $year ?? date('Y')
        ]);
    }
}
