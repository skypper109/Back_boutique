<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DetectShopNature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        $boutiqueId = $request->header('X-Boutique-Id');

        if (!$boutiqueId || $boutiqueId === 'null' || $boutiqueId === '') {
            $boutiqueId = $user->boutique_id;
        }

        if ($boutiqueId) {
            $boutique = \App\Models\Boutique::with('nature')->find($boutiqueId);

            if ($boutique && ($user->role === 'admin' || $boutique->user_id === $user->id || $user->boutique_id == $boutiqueId)) {
                // Attach to request for easy access in controllers
                $request->attributes->set('active_boutique', $boutique);
                
                // Add nature to response headers for frontend synchronization if needed
                $response = $next($request);
                if ($boutique->nature) {
                    $response->headers->set('X-Shop-Nature', $boutique->nature->slug);
                }
                return $response;
            }
        }

        return $next($request);
    }
}
