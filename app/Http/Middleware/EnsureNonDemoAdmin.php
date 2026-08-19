<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNonDemoAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if ($user && method_exists($user, 'isDemo') && $user->isDemo()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destructive operations are disabled in Demo Admin evaluation mode.',
                ], 403);
            }

            return back()->with('error', 'Destructive operations are disabled in Demo Admin evaluation mode.');
        }

        return $next($request);
    }
}
