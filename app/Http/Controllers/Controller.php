<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    /**
     * Obtenir l'ID de la boutique actuelle.
     * Si l'utilisateur est un admin, il peut spécifier une boutique via le header X-Boutique-Id.
     */
    protected function getBoutiqueId()
    {
        $user = Auth::user();
        if (!$user) {
            \Log::warning('getBoutiqueId called without authenticated user');
            return null;
        }

        $headerBoutiqueId = request()->header('X-Boutique-Id');

        // Priorité au header si présent et non vide
        if ($headerBoutiqueId && $headerBoutiqueId !== 'null' && $headerBoutiqueId !== '') {
            // Strict type validation
            if (!is_numeric($headerBoutiqueId)) {
                \Log::error("Invalid boutique_id type from header: {$headerBoutiqueId}");
                abort(400, 'ID boutique invalide');
            }

            $headerBoutiqueId = (int) $headerBoutiqueId;

            // Validate that this boutique exists and is active
            $boutique = \App\Models\Boutique::find($headerBoutiqueId);
            if (!$boutique) {
                \Log::error("Boutique not found: {$headerBoutiqueId} requested by user {$user->id}");
                abort(404, 'Boutique introuvable');
            }

            if (!$boutique->is_active) {
                \Log::warning("Attempt to access inactive boutique {$headerBoutiqueId} by user {$user->id}");
                abort(403, 'Boutique désactivée');
            }

            // Ensure user has permission (either owns it or is admin)
            if ($user->role !== 'admin' && $boutique->user_id !== $user->id && $user->boutique_id != $headerBoutiqueId) {
                \Log::error("User {$user->id} attempted unauthorized access to boutique {$headerBoutiqueId}");
                abort(403, 'Accès non autorisé à cette boutique');
            }

            \Log::info("User {$user->id} accessing boutique {$headerBoutiqueId} via header");
            return $headerBoutiqueId;
        }

        // Fallback to user's default boutique
        if (!$user->boutique_id) {
            \Log::warning("User {$user->id} has no boutique_id assigned");
        }

        return $user->boutique_id;
    }
}
