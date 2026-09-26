<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    /**
     * Obtenir l'objet boutique actuelle (chargé via middleware).
     */
    protected function getActiveBoutique()
    {
        $boutique = request()->attributes->get('active_boutique');
        
        if (!$boutique) {
            // Re-run logic if middleware somehow didn't run or failed (fallback)
            $boutiqueId = $this->getBoutiqueId();
            if ($boutiqueId) {
                $boutique = \App\Models\Boutique::with('nature')->find($boutiqueId);
                request()->attributes->set('active_boutique', $boutique);
            }
        }
        
        return $boutique;
    }

    /**
     * Obtenir l'ID de la boutique actuelle de manière sécurisée.
     */
    protected function getBoutiqueId()
    {
        $boutique = request()->attributes->get('active_boutique');
        if ($boutique) {
            return $boutique->id;
        }

        $user = Auth::user();
        if (!$user) return null;

        $headerBoutiqueId = request()->header('X-Boutique-Id');
        $requestedId = ($headerBoutiqueId && $headerBoutiqueId !== 'null' && $headerBoutiqueId !== '') 
            ? (int) $headerBoutiqueId 
            : null;

        // Seuls les administrateurs globaux peuvent basculer librement d'une boutique à une autre
        if (in_array($user->role, ['admin', 'admin1'])) {
            if ($requestedId) {
                return $requestedId;
            }
            if ($user->boutique_id) {
                return $user->boutique_id;
            }
            // Fallback automatique sur la première boutique existante pour le super admin
            return \App\Models\Boutique::first()?->id;
        }

        // Pour les autres rôles (vendeur, gestionnaire, comptable), la boutique assignée est impérative
        return $user->boutique_id ?: (\App\Models\Boutique::first()?->id);
    }
}
