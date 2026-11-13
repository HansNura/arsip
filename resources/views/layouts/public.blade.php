<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') — e-ARSIP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-chalk">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-full focus:bg-forest focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Lewati ke konten
    </a>

    <header class="border-b border-ink/10 bg-chalk/85 backdrop-blur-md">
        <div class="page-shell flex items-center justify-between gap-4 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="e-ARSIP, beranda">
                <span class="seal seal-sm">eA</span>
                <span class="leading-none">
                    <span class="block font-display text-xl font-semibold tracking-[-0.02em] text-ink">e-ARSIP</span>
                    <span class="mt-1 block text-[0.58rem] font-semibold tracking-[0.18em] text-ink/45 uppercase">Arsip sertifikat</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi publik">
                <a href="{{ route('home') }}" @class(['rounded-full px-4 py-2 text-sm font-medium transition', 'bg-ink/5 text-ink' => request()->routeIs('home'), 'text-ink/60 hover:bg-ink/5 hover:text-ink' => ! request()->routeIs('home')])>
                    Beranda
                </a>
                <a href="{{ route('verify.index') }}" @class(['rounded-full px-4 py-2 text-sm font-medium transition', 'bg-ink/5 text-ink' => request()->routeIs('verify.*'), 'text-ink/60 hover:bg-ink/5 hover:text-ink' => ! request()->routeIs('verify.*')])>
                    Verifikasi
                </a>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                        Buka dashboard
                        <x-icon name="arrow-right" class="size-4" />
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                        Masuk staf
                        <x-icon name="arrow-right" class="size-4" />
                    </a>
                @endauth
            </div>

            <button id="public-menu-toggle" type="button" class="btn btn-secondary btn-icon md:hidden" aria-controls="public-menu" aria-expanded="false" aria-label="Buka menu">
                <x-icon name="menu" class="size-5" />
            </button>
        </div>

        <div id="public-menu" class="hidden border-t border-ink/10 bg-paper md:hidden">
            <nav class="page-shell flex flex-col gap-1 py-4" aria-label="Navigasi publik mobile">
                <a href="{{ route('home') }}" class="rounded-xl px-3 py-2.5 text-sm font-medium text-ink/70 hover:bg-ink/5">Beranda</a>
                <a href="{{ route('verify.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-medium text-ink/70 hover:bg-ink/5">Verifikasi</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="mt-2 rounded-xl bg-forest px-3 py-2.5 text-sm font-semibold text-white">Buka dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="mt-2 rounded-xl bg-forest px-3 py-2.5 text-sm font-semibold text-white">Masuk staf</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main" class="flex-1">
        @if (session('status'))
            <div class="page-shell pt-6">
                <div class="alert alert-success" data-flash>
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-ink/10 bg-paper">
        <div class="page-shell grid gap-8 py-10 md:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <div class="flex items-center gap-3">
                    <span class="seal seal-sm">eA</span>
                    <span class="font-display text-lg font-semibold tracking-[-0.02em]">e-ARSIP</span>
                </div>
                <p class="mt-4 max-w-sm text-sm leading-6 text-ink/55">
                    Sistem arsip sertifikat pelatihan: data peserta, angkatan, nilai, penerbitan, verifikasi publik, retensi, dan jejak audit dalam satu tempat.
                </p>
            </div>
            <div>
                <p class="text-[0.64rem] font-semibold tracking-[0.18em] text-ink/40 uppercase">Tautan</p>
                <div class="mt-3 flex flex-col gap-2 text-sm text-ink/60">
                    <a href="{{ route('home') }}" class="hover:text-forest">Beranda</a>
                    <a href="{{ route('verify.index') }}" class="hover:text-forest">Verifikasi sertifikat</a>
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="hover:text-forest">{{ auth()->check() ? 'Dashboard' : 'Masuk staf' }}</a>
                </div>
            </div>
            <div>
                <p class="text-[0.64rem] font-semibold tracking-[0.18em] text-ink/40 uppercase">Privasi</p>
                <p class="mt-3 text-sm leading-6 text-ink/55">
                    Halaman publik hanya menampilkan nama, program, angkatan, dan tanggal terbit. NIK, alamat, dan kontak tidak pernah ditampilkan.
                </p>
            </div>
        </div>
        <div class="border-t border-ink/10">
            <div class="page-shell flex flex-col gap-2 py-4 text-xs text-ink/45 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} e-ARSIP. Sistem arsip sertifikat pelatihan.</p>
                <p class="font-mono text-[0.68rem]">FR-01 — FR-06</p>
            </div>
        </div>
    </footer>
</body>
</html>
