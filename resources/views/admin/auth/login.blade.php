<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Login | OrderFlow Lite</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 antialiased text-slate-100 bg-slate-900 selection:bg-indigo-500 selection:text-white">

    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 shadow-xl shadow-indigo-500/20 text-white font-black text-2xl mb-4">
                OF
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">OrderFlow Lite</h1>
            <p class="text-sm text-slate-400 mt-1">SaaS Order Management Administrative Panel</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-800/90 backdrop-blur border border-slate-700/80 rounded-2xl p-6 sm:p-8 shadow-2xl">
            <h2 class="text-lg font-semibold text-white mb-6">Administrator Sign In</h2>

            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-500/10 border border-red-500/30 p-3.5 text-xs text-red-400">
                    <div class="font-medium mb-1">Authentication Error:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-5 rounded-lg bg-indigo-500/10 border border-indigo-500/30 p-3.5 text-xs text-indigo-300">
                    {{ session('info') }}
                </div>
            @endif

            <!-- Formal Admin Google SSO Section -->
            <div class="mb-6">
                <div class="text-xs font-medium text-slate-400 mb-2">Formal Administrator (Supabase SSO)</div>
                <div id="supabase-sso-area" class="space-y-3">
                    <button type="button"
                            id="google-sso-btn"
                            onclick="handleGoogleSsoClick()"
                            class="w-full py-2.5 px-4 bg-white hover:bg-slate-100 text-slate-900 text-sm font-semibold rounded-lg shadow-sm border border-slate-300 transition flex items-center justify-center space-x-2.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Sign in with Google (Supabase Auth)</span>
                    </button>
                </div>
            </div>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-700"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-slate-800 px-2 text-slate-400 font-medium">Or Demo Evaluation</span>
                </div>
            </div>

            @if ($demoEnabled)
                <!-- Demo Login Form -->
                <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">Demo Admin Email</label>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email', $demoEmail) }}"
                               required
                               placeholder="demo@example.com"
                               class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Demo Password</label>
                        <input type="password"
                               name="password"
                               id="password"
                               required
                               placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg shadow-lg shadow-indigo-600/30 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        Sign In as Demo Admin
                    </button>
                </form>
            @else
                <div class="p-3.5 bg-slate-900/50 rounded-lg border border-slate-700/50 text-xs text-slate-400 text-center">
                    Demo Admin access is currently disabled by server environment policy.
                </div>
            @endif
        </div>

        <div class="text-center mt-6 text-xs text-slate-500">
            OrderFlow Lite &times; Supabase PostgreSQL Shared Architecture
        </div>
    </div>

    <!-- Hidden form for Supabase token submission -->
    <form id="supabase-token-form" method="POST" action="{{ route('admin.login.supabase') }}" class="hidden">
        @csrf
        <input type="hidden" name="access_token" id="supabase-access-token">
    </form>

    <script>
        function handleGoogleSsoClick() {
            const supabaseUrl = "{{ env('SUPABASE_URL') }}";
            const anonKey = "{{ env('SUPABASE_ANON_KEY') }}";

            if (!supabaseUrl) {
                alert('SUPABASE_URL is not configured in server environment.');
                return;
            }

            // Redirect to Supabase OAuth Google authorize URL
            const redirectUrl = window.location.origin + '/admin/login';
            const authUrl = `${supabaseUrl.replace(/\/$/, '')}/auth/v1/authorize?provider=google&redirect_to=${encodeURIComponent(redirectUrl)}`;
            window.location.href = authUrl;
        }

        // Handle OAuth callback access_token hash if present in URL
        window.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash) {
                const params = new URLSearchParams(window.location.hash.substring(1));
                const token = params.get('access_token');
                if (token) {
                    document.getElementById('supabase-access-token').value = token;
                    document.getElementById('supabase-token-form').submit();
                }
            }
        });
    </script>
</body>
</html>
