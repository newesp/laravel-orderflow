<?php

namespace App\Services;

use App\Exceptions\ForbiddenAdminException;
use App\Models\AdminSessionUser;
use App\Models\Profile;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SupabaseJwksService
{
    public function getSupabaseUrl(): string
    {
        $supabaseUrl = config('services.supabase.url') ?? env('SUPABASE_URL', '');

        if (empty($supabaseUrl)) {
            throw new RuntimeException('SUPABASE_URL environment variable is not configured.');
        }

        return rtrim($supabaseUrl, '/');
    }

    public function getJwksUrl(): string
    {
        return $this->getSupabaseUrl() . '/auth/v1/.well-known/jwks.json';
    }

    public function fetchJwks(): array
    {
        $cacheKey = 'supabase_jwks_keyset';

        return Cache::remember($cacheKey, 3600, function () {
            $jwksUrl = $this->getJwksUrl();
            $response = Http::timeout(5)->get($jwksUrl);

            if (!$response->successful()) {
                throw new RuntimeException("Failed to fetch Supabase JWKS from {$jwksUrl}: HTTP {$response->status()}");
            }

            return $response->json();
        });
    }

    /**
     * Validate Supabase Access Token against Supabase JWKS and check profile admin role.
     *
     * Validates:
     * - signature valid (via JWKS public key)
     * - exp valid
     * - iss = {SUPABASE_URL}/auth/v1
     * - aud = authenticated
     * - sub exists
     *
     * @throws ForbiddenAdminException
     * @throws RuntimeException
     */
    public function validateToken(string $accessToken): AdminSessionUser
    {
        try {
            $jwks = $this->fetchJwks();
            $keys = JWK::parseKeySet($jwks);

            // JWT::decode validates signature, exp, nbf, and format
            $decoded = JWT::decode($accessToken, $keys);
        } catch (Throwable $e) {
            Log::warning('Supabase JWT verification failed: ' . $e->getMessage());
            throw new RuntimeException('Invalid or expired Supabase access token: ' . $e->getMessage(), 401, $e);
        }

        // Validate sub exists
        $userId = $decoded->sub ?? null;
        if (empty($userId)) {
            throw new RuntimeException('Supabase access token is missing sub claim.', 401);
        }

        // Validate iss = {SUPABASE_URL}/auth/v1
        $expectedIssuer = $this->getSupabaseUrl() . '/auth/v1';
        if (!isset($decoded->iss) || $decoded->iss !== $expectedIssuer) {
            throw new RuntimeException("Invalid token issuer: '{$decoded->iss}', expected '{$expectedIssuer}'.", 401);
        }

        // Validate aud = authenticated
        if (!isset($decoded->aud) || $decoded->aud !== 'authenticated') {
            $audString = is_array($decoded->aud ?? null) ? implode(',', $decoded->aud) : (string) ($decoded->aud ?? '');
            if ($audString !== 'authenticated') {
                throw new RuntimeException("Invalid token audience: '{$audString}', expected 'authenticated'.", 401);
            }
        }

        // Query public.profiles using validated sub (user_id)
        $profile = Profile::where('id', $userId)->first();

        if (!$profile || $profile->role !== 'admin') {
            throw new ForbiddenAdminException("User '{$userId}' does not have administrator privileges in profiles table.");
        }

        return new AdminSessionUser(
            id: (string) $userId,
            email: (string) ($decoded->email ?? ($profile->display_name . '@supabase.user')),
            name: (string) ($profile->display_name ?? 'Admin'),
            role: 'admin',
            is_demo: false
        );
    }
}
