<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Login | OrderFlow Lite</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

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
            <h2 class="text-lg font-semibold text-white mb-6">Sign in to your account</h2>

            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-500/10 border border-red-500/30 p-3.5 text-xs text-red-400">
                    <div class="font-medium mb-1">Please correct the following errors:</div>
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

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">Email Address</label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           placeholder="admin@example.com"
                           class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Password</label>
                    <input type="password"
                           name="password"
                           id="password"
                           required
                           placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center space-x-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg shadow-lg shadow-indigo-600/30 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-800 transition">
                    Sign In
                </button>
            </form>

            <!-- 1-Click Demo Fill Box -->
            <div class="mt-6 pt-5 border-t border-slate-700/60">
                <div class="bg-indigo-950/40 border border-indigo-500/20 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-indigo-300 uppercase tracking-wider">Demo Account</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold bg-indigo-500/20 text-indigo-300 rounded">Evaluation</span>
                    </div>
                    <div class="text-xs text-slate-400 space-y-1 mb-3">
                        <div>Email: <code class="text-indigo-200">demo@example.com</code></div>
                        <div>Password: <code class="text-indigo-200">demo1234</code></div>
                    </div>
                    <button type="button"
                            id="demo-fill-btn"
                            class="w-full py-1.5 px-3 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-medium rounded-lg border border-slate-600 transition flex items-center justify-center space-x-1.5">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Auto-Fill Demo Credentials</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-500">
            OrderFlow Lite &times; Supabase PostgreSQL Shared Database
        </div>
    </div>

    <script>
        document.getElementById('demo-fill-btn')?.addEventListener('click', function() {
            document.getElementById('email').value = 'demo@example.com';
            document.getElementById('password').value = 'demo1234';
        });
    </script>
</body>
</html>
