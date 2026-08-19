<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ForbiddenAdminException;
use App\Http\Controllers\Controller;
use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        protected AdminAuthService $adminAuthService
    ) {}

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        $demoEnabled = $this->adminAuthService->isDemoEnabled();
        $demoEmail = $demoEnabled ? (string) config('admin.demo.email') : '';

        return view('admin.auth.login', compact('demoEnabled', 'demoEmail'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->adminAuthService->attemptDemoLogin($credentials['email'], $credentials['password']);

        if ($user) {
            $this->adminAuthService->loginSession($request, $user);
            Auth::guard('admin')->login($user);

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials or demo access is disabled.',
        ])->onlyInput('email');
    }

    public function loginWithSupabase(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'access_token' => ['required', 'string'],
        ]);

        $token = $request->input('access_token');

        try {
            $user = $this->adminAuthService->attemptSupabaseTokenLogin($token);
            $this->adminAuthService->loginSession($request, $user, $token);
            Auth::guard('admin')->login($user);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Authenticated successfully as Administrator',
                    'data' => $user->toArray(),
                ]);
            }

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        } catch (ForbiddenAdminException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 403);
            }
            return back()->withErrors(['email' => $e->getMessage()]);
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supabase authentication failed: ' . $e->getMessage(),
                ], 401);
            }
            return back()->withErrors(['email' => 'Invalid Supabase authentication token.']);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $this->adminAuthService->logoutSession($request);

        return redirect()->route('admin.login')
            ->with('info', 'You have been successfully logged out.');
    }

    public function apiLogin(Request $request): JsonResponse
    {
        // 1. Check if Supabase access token provided
        if ($token = $request->input('access_token')) {
            return $this->loginWithSupabase($request);
        }

        // 2. Demo credentials login
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->adminAuthService->attemptDemoLogin($credentials['email'], $credentials['password']);

        if ($user) {
            $this->adminAuthService->loginSession($request, $user);
            Auth::guard('admin')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => $user->toArray(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid administrative credentials or demo access is disabled.',
        ], 401);
    }

    public function apiLogout(Request $request): JsonResponse
    {
        Auth::guard('admin')->logout();
        $this->adminAuthService->logoutSession($request);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function apiMe(Request $request): JsonResponse
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $admin->getAuthIdentifier(),
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'is_demo' => $admin->is_demo,
            ],
        ]);
    }
}
