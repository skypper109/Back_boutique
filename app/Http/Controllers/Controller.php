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

        if ($user->role === 'admin' && request()->header('X-Boutique-Id')) {
            return request()->header('X-Boutique-Id');
        }

        return $user->boutique_id;
    }
}
