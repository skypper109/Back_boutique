<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompteComptable;
use App\Models\JournalComptable;
use App\Models\EcritureComptable;
use App\Services\ComptaService;
use Illuminate\Support\Facades\Auth;

class ComptabiliteController extends Controller
{
    protected ComptaService $comptaService;

    public function __construct(ComptaService $comptaService)
    {
        $this->comptaService = $comptaService;
    }

    /**
     * Liste des comptes du plan comptable SYSCOHADA (système + boutique)
     */
    public function comptes(Request $request)
    {
        $boutiqueId = $this->getBoutiqueId();

        $query = CompteComptable::where(function ($q) use ($boutiqueId) {
            $q->whereNull('boutique_id');
            if ($boutiqueId) {
                $q->orWhere('boutique_id', $boutiqueId);
            }
        });

        if ($request->has('classe') && $request->classe) {
            $query->where('classe', $request->classe);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhere('libelle', 'like', "%{$search}%");
            });
        }

        $comptes = $query->orderBy('numero', 'asc')->get();

        return response()->json($comptes, 200);
    }

    /**
     * Ajouter un sous-compte personnalisé pour la boutique
     */
    public function storeCompte(Request $request)
    {
        $boutiqueId = $this->getBoutiqueId();
        if (!$boutiqueId) {
            return response()->json(['message' => 'Boutique non identifiée.'], 400);
        }

        $request->validate([
            'numero' => 'required|string|min:4|max:20',
            'libelle' => 'required|string|max:255',
            'classe' => 'required|integer|between:1,8',
            'type' => 'required|in:actif,passif,charge,produit',
            'sens_normal' => 'required|in:debit,credit',
        ]);

        $exists = CompteComptable::where('numero', $request->numero)
            ->where(function ($q) use ($boutiqueId) {
                $q->where('boutique_id', $boutiqueId)->orWhereNull('boutique_id');
            })
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ce numéro de compte existe déjà.'], 422);
        }

        $compte = CompteComptable::create([
            'boutique_id' => $boutiqueId,
            'numero' => $request->numero,
            'libelle' => $request->libelle,
            'classe' => $request->classe,
            'type' => $request->type,
            'sens_normal' => $request->sens_normal,
            'is_system' => false,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Compte comptable créé avec succès.',
            'compte' => $compte,
        ], 201);
    }

    /**
     * Liste des journaux comptables
     */
    public function journaux(Request $request)
    {
        $boutiqueId = $this->getBoutiqueId();

        $journaux = JournalComptable::where(function ($q) use ($boutiqueId) {
            $q->whereNull('boutique_id');
            if ($boutiqueId) {
                $q->orWhere('boutique_id', $boutiqueId);
            }
        })->get();

        return response()->json($journaux, 200);
    }

    /**
     * Liste des écritures comptables avec filtres
     */
    public function ecritures(Request $request)
    {
        $boutiqueId = $this->getBoutiqueId();
        if (!$boutiqueId) {
            return response()->json(['message' => 'Boutique non identifiée.'], 400);
        }

        $query = EcritureComptable::with(['journal', 'lignes.compte', 'user'])
            ->where('boutique_id', $boutiqueId);

        if ($request->has('journal_id') && $request->journal_id) {
            $query->where('journal_id', $request->journal_id);
        }

        if ($request->has('date_debut') && $request->date_debut) {
            $query->where('date_ecriture', '>=', $request->date_debut);
        }

        if ($request->has('date_fin') && $request->date_fin) {
            $query->where('date_ecriture', '<=', $request->date_fin);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_piece', 'like', "%{$search}%")
                  ->orWhere('libelle', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 25);
        $ecritures = $query->orderBy('date_ecriture', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($ecritures, 200);
    }

    /**
     * Passer une écriture comptable manuelle (ex: Opérations Diverses, apport personnel, régularisation)
     */
    public function storeEcriture(Request $request)
    {
        $boutiqueId = $this->getBoutiqueId();
        if (!$boutiqueId) {
            return response()->json(['message' => 'Boutique non identifiée.'], 400);
        }

        $request->validate([
            'journal_code' => 'required|string|max:10',
            'date_ecriture' => 'required|date',
            'libelle' => 'required|string|max:255',
            'lignes' => 'required|array|min:2',
            'lignes.*.compte_id' => 'required|exists:comptes_comptables,id',
            'lignes.*.debit' => 'required|numeric|min:0',
            'lignes.*.credit' => 'required|numeric|min:0',
            'lignes.*.libelle' => 'nullable|string|max:255',
        ]);

        try {
            $ecriture = $this->comptaService->createEcriture(
                $boutiqueId,
                $request->journal_code,
                $request->date_ecriture,
                $request->libelle,
                $request->lignes,
                'Manuel',
                null,
                Auth::id()
            );

            return response()->json([
                'message' => 'Écriture comptable enregistrée avec succès.',
                'ecriture' => $ecriture,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Balance générale SYSCOHADA (6 colonnes)
     */
    public function balance(Request $request)
    {
        $boutiqueId = $this->getBoutiqueId();
        if (!$boutiqueId) {
            return response()->json(['message' => 'Boutique non identifiée.'], 400);
        }

        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');

        $balance = $this->comptaService->getBalanceGenerale($boutiqueId, $dateDebut, $dateFin);

        return response()->json($balance, 200);
    }

    /**
     * Grand Livre des comptes SYSCOHADA
     */
    public function grandLivre(Request $request)
    {
        $boutiqueId = $this->getBoutiqueId();
        if (!$boutiqueId) {
            return response()->json(['message' => 'Boutique non identifiée.'], 400);
        }

        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $compteId = $request->input('compte_id');

        $grandLivre = $this->comptaService->getGrandLivre($boutiqueId, $dateDebut, $dateFin, $compteId);

        return response()->json($grandLivre, 200);
    }

    /**
     * Compte de Résultat SYSCOHADA (Produits Classe 7 - Charges Classe 6)
     */
    public function compteResultat(Request $request)
    {
        $boutiqueId = $this->getBoutiqueId();
        if (!$boutiqueId) {
            return response()->json(['message' => 'Boutique non identifiée.'], 400);
        }

        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');

        $resultat = $this->comptaService->getCompteResultat($boutiqueId, $dateDebut, $dateFin);

        return response()->json($resultat, 200);
    }

    /**
     * Bilan comptable SYSCOHADA (Actif vs Passif)
     */
    public function bilan(Request $request)
    {
        $boutiqueId = $this->getBoutiqueId();
        if (!$boutiqueId) {
            return response()->json(['message' => 'Boutique non identifiée.'], 400);
        }

        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');

        $bilan = $this->comptaService->getBilan($boutiqueId, $dateDebut, $dateFin);

        return response()->json($bilan, 200);
    }
}
