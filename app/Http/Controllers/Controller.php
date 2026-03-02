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
     * Obtenir l'ID de la boutique actuelle.
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
        return ($headerBoutiqueId && $headerBoutiqueId !== 'null' && $headerBoutiqueId !== '') 
            ? (int) $headerBoutiqueId 
            : $user->boutique_id;
    }

    /**
     * Obtenir la stratégie de nature pour la boutique actuelle.
     */
    protected function getNatureStrategy(): \App\Contracts\ShopNatureStrategy
    {
        $boutique = $this->getActiveBoutique();
        if (!$boutique) {
            // Fallback to default if no boutique context
            return \App\Services\NatureStrategyFactory::makeBySlug('default');
        }

        return \App\Services\NatureStrategyFactory::make($boutique);
    }
}
