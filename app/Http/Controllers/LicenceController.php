<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Licence;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LicenceController extends Controller
{
    /**
     * Valide et applique une clé de licence sur la boutique
     * Route publique (permet l'activation même si la session a expiré)
     */
    public function activer(Request $request)
    {
        $request->validate([
            'cle_licence' => 'required|string|min:8|max:64',
        ]);

        $cle = strtoupper(trim($request->cle_licence));

        $licence = Licence::where('cle_licence', $cle)->first();

        if (!$licence) {
            return response()->json([
                'error' => 'invalid_key',
                'message' => 'Clé d\'activation invalide ou inexistante. Veuillez vérifier votre saisie.'
            ], 404);
        }

        if ($licence->statut === 'revoquee') {
            return response()->json([
                'error' => 'revoked_key',
                'message' => 'Cette clé d\'activation a été révoquée. Veuillez contacter votre administrateur.'
            ], 403);
        }

        if ($licence->statut === 'active' || $licence->statut === 'expiree') {
            return response()->json([
                'error' => 'already_used',
                'message' => 'Cette clé d\'activation a déjà été utilisée le ' . ($licence->date_activation ? $licence->date_activation->format('d/m/Y') : '') . '.'
            ], 400);
        }

        $boutique = Boutique::find($licence->boutique_id);

        if (!$boutique) {
            return response()->json([
                'error' => 'boutique_not_found',
                'message' => 'L\'établissement associé à cette clé n\'a pas été trouvé.'
            ], 404);
        }

        DB::transaction(function () use ($licence, $boutique) {
            $isUnlimited = $licence->duree_jours >= 90000;

            if ($isUnlimited) {
                // Licence illimitée / à vie
                $newExpiration = null;
            } else {
                // Si la boutique a déjà une date d'expiration future, on prolonge à partir de cette date
                $baseDate = ($boutique->date_expiration_licence && Carbon::parse($boutique->date_expiration_licence)->isFuture())
                    ? Carbon::parse($boutique->date_expiration_licence)
                    : Carbon::now();

                $newExpiration = $baseDate->addDays($licence->duree_jours);
            }

            // Mettre à jour la licence
            $licence->statut = 'active';
            $licence->date_activation = Carbon::now();
            $licence->date_expiration = $newExpiration;
            $licence->save();

            // Mettre à jour la boutique
            $boutique->date_expiration_licence = $newExpiration;
            $boutique->is_active = true;
            $boutique->save();
        });

        $formattedExp = $boutique->date_expiration_licence
            ? Carbon::parse($boutique->date_expiration_licence)->format('d/m/Y à H:i')
            : 'Accès permanent (À vie)';

        return response()->json([
            'success' => true,
            'message' => "Licence activée avec succès pour la boutique « {$boutique->nom} » !",
            'boutique_id' => $boutique->id,
            'boutique_nom' => $boutique->nom,
            'date_expiration' => $boutique->date_expiration_licence,
            'date_expiration_formatee' => $formattedExp,
            'is_unlimited' => $licence->duree_jours >= 90000,
            'jours_ajoutes' => $licence->duree_jours,
        ], 200);
    }

    /**
     * Récupère l'état de la licence d'une boutique
     */
    public function statut(Request $request, $boutique_id)
    {
        $boutique = Boutique::find($boutique_id);

        if (!$boutique) {
            return response()->json(['error' => 'Boutique introuvable.'], 404);
        }

        return response()->json([
            'boutique_id' => $boutique->id,
            'boutique_nom' => $boutique->nom,
            'is_active' => (bool) $boutique->is_active,
            'is_expired' => $boutique->isLicenceExpired(),
            'date_expiration' => $boutique->date_expiration_licence,
            'jours_restants' => $boutique->joursRestants(),
        ], 200);
    }
}
