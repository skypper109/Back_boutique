<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureBoutiqueIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $boutiqueId = $request->header('X-Boutique-Id') ?: (Auth::user()?->boutique_id);

        \Log::debug('EnsureBoutiqueIsActive middleware', [
            'header_boutique_id' => $request->header('X-Boutique-Id'),
            'user_boutique_id' => Auth::user()?->boutique_id,
            'resolved_boutique_id' => $boutiqueId,
            'user_id' => Auth::id()
        ]);

        if ($boutiqueId && $boutiqueId !== 'null' && $boutiqueId !== '') {
            $boutique = \App\Models\Boutique::find($boutiqueId);
            if ($boutique && !$boutique->is_active) {
                \Log::warning("Access denied to inactive boutique {$boutiqueId} by user " . Auth::id());
                return response()->json([
                    'error' => 'Cette boutique est désactivée. Accès refusé.'
                ], 403);
            }
            if (!$boutique) {
                \Log::error("Boutique {$boutiqueId} not found in middleware");
            }
        }

        return $next($request);
    }
}
