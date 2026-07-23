<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use App\Models\User;

class VerifySupabaseJWT
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Get Supabase JWT secret from environment
            $jwtSecret = env('SUPABASE_JWT_SECRET');

            if (!$jwtSecret) {
                return response()->json(['error' => 'JWT secret not configured'], 500);
            }

            // Decode and verify the JWT
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

            // Extract user ID from JWT payload
            $userId = $decoded->sub ?? null;
            $email = $decoded->email ?? null;

            if (!$userId) {
                return response()->json(['error' => 'Invalid token payload'], 401);
            }

            // Find or create user in local database
            $user = User::firstOrCreate(
                ['id' => $userId],
                [
                    'email' => $email,
                    'name' => $decoded->user_metadata->name ?? null,
                ]
            );

            // Attach user to request
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        return $next($request);
    }
}
