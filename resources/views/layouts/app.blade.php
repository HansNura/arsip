<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') — e-ARSIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50 text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-xl bg-emerald-700 text-sm font-bold text-white">eA</span>
                <span class="text-base font-semibold tracking-tight">e-ARSIP</span>
            </a>

            <nav class="flex items-center gap-1 text-sm">
                <a href="{{ route('verify.index') }}" class="rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                    Verifikasi
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                        Dashboard
                    </a>

                    <a href="{{ route('profile.edit') }}" class="rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                        Profil
                    </a>

                    @if (auth()->user()->role === \App\Enums\UserRole::Admin)
                        <a href="{{ route('admin.users.index') }}" class="rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                            Pengguna
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="ml-1">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="ml-1 rounded-lg bg-emerald-700 px-3.5 py-2 font-medium text-white transition hover:bg-emerald-800">
                        Masuk
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            @if (session('status'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p>&copy; {{ date('Y') }} e-ARSIP. Sistem arsip sertifikat pelatihan.</p>
            <p>Verifikasi keaslian sertifikat dapat dilakukan tanpa login.</p>
        </div>
    </footer>
</body>
</html>
