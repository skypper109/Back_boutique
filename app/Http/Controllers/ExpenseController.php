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
        
        $user = Auth::user();
        if ($user->role !== 'admin') {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($request->has('boutique_id')) {
            $query->where('boutique_id', $request->boutique_id);
        }

        if ($request->has('month')) {
            $query->whereMonth('date', $request->month);
        }
        if ($request->has('year')) {
            $query->whereYear('date', $request->year);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderByDesc('date')->paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'type' => 'required|string',
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'boutique_id' => 'required|exists:boutiques,id',
        ]);

        $fields['user_id'] = Auth::id();

        $expense = Expense::create($fields);

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

        $expense->update($fields);

        return response()->json($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(['message' => 'Dépense supprimée']);
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $boutiqueId = $user->role === 'admin' ? $request->boutique_id : $user->boutique_id;
        
        if (!$boutiqueId && $user->role !== 'admin') {
            return response()->json(['message' => 'Boutique n\'existe'], 400);
        }

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
            'total_year' => $totalByYear || null,
            'monthly_evolution' => $monthlyEvolution || null,
            'breakdown_by_type' => $breakdownByType || null,
            'year' => $year || null
        ]);
    }
}
