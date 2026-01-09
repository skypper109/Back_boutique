<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    /**
     * Obtenir l'ID de la boutique actuelle.
     * Si l'utilisateur est un admin, il peut spécifier une boutique via le header X-Boutique-Id.
     */
    protected function getBoutiqueId()
    {
        $user = Auth::user();
        if (!$user) return null;

        $headerBoutiqueId = request()->header('X-Boutique-Id');

        // Priorité au header si présent et non vide
        if ($headerBoutiqueId && $headerBoutiqueId !== 'null' && $headerBoutiqueId !== '') {
            return $headerBoutiqueId;
        }

        return $user->boutique_id;
    }
}
