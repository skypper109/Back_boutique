<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\Client;
use App\Models\PaiementCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    /**
     * Liste des ventes à crédit.
     */
    public function index()
    {
        $boutique_id = $this->getBoutiqueId();
        $credits = Vente::with(['client', 'user'])
            ->where('boutique_id', $boutique_id)
            ->where('type_paiement', 'credit')
            ->where('montant_restant', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($credits, 200);
    }

    /**
     * Liste des débiteurs (clients avec une dette totale).
     */
    public function debtors()
    {
        $boutique_id = $this->getBoutiqueId();
        $debtors = Client::whereHas('ventes', function ($q) use ($boutique_id) {
            $q->where('boutique_id', $boutique_id)
                ->where('type_paiement', 'credit')
                ->where('montant_restant', '>', 0);
        })
            ->withSum(['ventes as total_dette' => function ($q) use ($boutique_id) {
                $q->where('boutique_id', $boutique_id)
                    ->where('type_paiement', 'credit');
            }], 'montant_restant')
            ->get();

        return response()->json($debtors, 200);
    }

    /**
     * Enregistrer un nouveau paiement pour un crédit.
     */
    public function addPayment(Request $request)
    {
        $request->validate([
            'vente_id' => 'required|exists:ventes,id',
            'montant' => 'required|numeric|min:0.01',
            'date_paiement' => 'required|date',
            'mode_paiement' => 'string|nullable',
            'notes' => 'string|nullable',
        ]);

        $boutique_id = $this->getBoutiqueId();
        $vente = Vente::where('id', $request->vente_id)
            ->where('boutique_id', $boutique_id)
            ->firstOrFail();

        if ($request->montant > $vente->montant_restant) {
            return response()->json(['message' => 'Le montant du paiement dépasse le reste à payer'], 400);
        }

        DB::beginTransaction();
        try {
            $paiement = PaiementCredit::create([
                'vente_id' => $vente->id,
                'boutique_id' => $boutique_id,
                'user_id' => Auth::id(),
                'montant' => $request->montant,
                'date_paiement' => $request->date_paiement,
                'mode_paiement' => $request->mode_paiement ?? 'Espèces',
                'notes' => $request->notes,
            ]);

            $vente->montant_restant -= $request->montant;
            $vente->save();

            DB::commit();
            return response()->json(['message' => 'Paiement enregistré avec succès', 'paiement' => $paiement], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de l\'enregistrement du paiement', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Données pour le bordereau de paiement.
     */
    public function saleStatement($id)
    {
        $boutique_id = $this->getBoutiqueId();
        $vente = Vente::with(['client', 'user', 'detailVentes.produit', 'paiementsCredit.user'])
            ->where('id', $id)
            ->where('boutique_id', $boutique_id)
            ->firstOrFail();

        return response()->json($vente, 200);
    }
}
