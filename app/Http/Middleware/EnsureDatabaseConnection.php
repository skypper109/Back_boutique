<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureDatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Test database connection
            \DB::connection()->getPdo();

            // Verify migrations table exists
            if (!\Schema::hasTable('migrations')) {
                Log::error('Migrations table missing - database may not be initialized');
                return response()->json([
                    'error' => 'Database initialization error',
                    'message' => 'Please restart the application'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Database connection failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Database connection error',
                'message' => 'Unable to connect to database'
            ], 500);
        }

        return $next($request);
    }
}
