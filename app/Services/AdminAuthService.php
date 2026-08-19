<?php

namespace App\Services;

use App\Models\AdminSessionUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuthService
{
    public function __construct(
        protected SupabaseJwksService $supabaseJwksService
    ) {}

    public function isDemoEnabled(): bool
    {
        return config('admin.demo.enabled');
    }

    public function attemptDemoLogin(string $email, string $password): ?AdminSessionUser
    {
        if (!$this->isDemoEnabled()) {
            return null;
        }

        $expectedEmail = (string) config('admin.demo.email');
        $expectedPassword = (string) config('admin.demo.password');

        if (empty($expectedEmail) || empty($expectedPassword)) {
            return null;
        }

        // Timing-attack safe comparisons
        $emailMatches = hash_equals($expectedEmail, $email);
        $passwordMatches = hash_equals($expectedPassword, $password);

        if ($emailMatches && $passwordMatches) {
            return new AdminSessionUser(
                id: 'demo-admin-session',
                email: $email,
                name: 'Demo Administrator',
                role: 'admin',
                is_demo: true
            );
        }

        return null;
    }

    public function attemptSupabaseTokenLogin(string $accessToken): AdminSessionUser
    {
        return $this->supabaseJwksService->validateToken($accessToken);
    }

    public function loginSession(Request $request, AdminSessionUser $user, ?string $token = null): void
    {
        $request->session()->put('admin_user', $user->toArray());
        
        if ($token) {
            $request->session()->put('supabase_access_token', $token);
        }
        
        $request->session()->regenerate();
    }

    public function logoutSession(Request $request): void
    {
        $request->session()->forget('admin_user');
        $request->session()->forget('supabase_access_token');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function getCurrentUser(Request $request): ?AdminSessionUser
    {
        $data = $request->session()->get('admin_user');

        if (is_array($data) && !empty($data['id'])) {
            return AdminSessionUser::fromArray($data);
        }

        return null;
    }
}
