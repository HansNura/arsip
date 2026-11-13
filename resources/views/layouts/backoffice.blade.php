@php
    $user = auth()->user();
    $initials = collect(explode(' ', trim((string) $user->nama)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');
    $roleLabel = $user->role === \App\Enums\UserRole::Admin ? 'Admin' : 'Staf Arsip';
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — e-ARSIP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-chalk">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[70] focus:rounded-full focus:bg-forest focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Lewati ke konten
    </a>

    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-ink/45 backdrop-blur-sm lg:hidden" data-sidebar-close></div>

    <div class="lg:flex">
        <aside id="app-sidebar"
               class="sidebar fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col text-paper transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:translate-x-0"
               aria-label="Navigasi utama">
            <div class="flex items-center justify-between px-5 py-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3" aria-label="e-ARSIP, dashboard">
                    <span class="seal seal-sm">eA</span>
                    <span class="leading-none">
                        <span class="block font-display text-lg font-semibold tracking-[-0.02em] text-white">e-ARSIP</span>
                        <span class="mt-1 block text-[0.56rem] font-semibold tracking-[0.18em] text-paper/40 uppercase">Ruang kerja arsip</span>
                    </span>
                </a>
                <button type="button" class="btn-icon text-paper/60 hover:bg-white/10 hover:text-white lg:hidden" data-sidebar-close aria-label="Tutup menu">
                    <x-icon name="close" class="size-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-3 pb-5">
                <p class="nav-group-label">Ruang Kerja</p>
                <nav class="mt-2 space-y-1" aria-label="Ruang kerja">
                    <x-nav-link href="{{ route('dashboard') }}" icon="grid" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                    <x-nav-link href="{{ route('admin.participants.index') }}" icon="search" :active="request()->routeIs('admin.participants.*')">Peserta</x-nav-link>
                    <x-nav-link href="{{ route('admin.batches.index') }}" icon="layers" :active="request()->routeIs('admin.batches.*')">Angkatan</x-nav-link>
                    <x-nav-link href="{{ route('admin.programs.index') }}" icon="book" :active="request()->routeIs('admin.programs.*')">Program</x-nav-link>
                    <x-nav-link href="{{ route('admin.certificates.index') }}" icon="certificate" :active="request()->routeIs('admin.certificates.*')">Sertifikat</x-nav-link>
                    <x-nav-link href="{{ route('admin.reissues.index') }}" icon="reissue" :active="request()->routeIs('admin.reissues.*')">Penerbitan Ulang</x-nav-link>
                    <x-nav-link href="{{ route('admin.reports.graduation') }}" icon="chart" :active="request()->routeIs('admin.reports.*')">Laporan</x-nav-link>
                </nav>

                @if ($user->role === \App\Enums\UserRole::Admin)
                    <p class="nav-group-label mt-6">Administrasi</p>
                    <nav class="mt-2 space-y-1" aria-label="Administrasi">
                        <x-nav-link href="{{ route('admin.users.index') }}" icon="users" :active="request()->routeIs('admin.users.*')">Pengguna</x-nav-link>
                        <x-nav-link href="{{ route('admin.retention.index') }}" icon="archive" :active="request()->routeIs('admin.retention.*')">Retensi</x-nav-link>
                        <x-nav-link href="{{ route('admin.certificate-templates.edit') }}" icon="settings" :active="request()->routeIs('admin.certificate-templates.*')">Template Sertifikat</x-nav-link>
                    </nav>
                @endif

                <div class="mt-6 border-t border-white/10 pt-4">
                    <x-nav-link href="{{ route('verify.index') }}" icon="shield">Verifikasi Publik</x-nav-link>
                </div>
            </div>

            <div class="border-t border-white/10 p-4">
                <div class="flex items-center gap-3">
                    <span class="seal seal-sm">{{ $initials }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ $user->nama }}</p>
                        <p class="mt-0.5 text-[0.6rem] font-semibold tracking-[0.12em] text-paper/40 uppercase">{{ $roleLabel }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                        @csrf
                        <button type="submit" class="btn-icon text-paper/50 hover:bg-white/10 hover:text-white" aria-label="Keluar">
                            <x-icon name="logout" class="size-[1.1rem]" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
            <header class="topbar">
                <div class="flex items-center gap-3 px-4 py-3 sm:px-6 lg:px-10">
                    <button id="sidebar-open" type="button" class="btn btn-secondary btn-icon lg:hidden" aria-controls="app-sidebar" aria-expanded="false" aria-label="Buka menu">
                        <x-icon name="menu" class="size-5" />
                    </button>

                    <nav class="min-w-0 text-sm" aria-label="Breadcrumb">
                        <span class="text-ink/40">e-ARSIP</span>
                        <span class="mx-2 text-ink/20">/</span>
                        <span class="truncate font-medium text-ink">@yield('title', 'Dashboard')</span>
                    </nav>

                    <div class="ml-auto flex items-center gap-2">
                        <a href="{{ route('verify.index') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex">
                            <x-icon name="shield" class="size-4" />
                            Verifikasi
                        </a>
                        <span class="grid size-9 place-items-center rounded-full border border-forest/15 bg-forest/10 text-xs font-bold text-forest" aria-hidden="true">
                            {{ $initials }}
                        </span>
                    </div>
                </div>
            </header>

            <main id="main" class="flex-1">
                <div class="page-shell py-8 lg:py-10">
                    @if (session('status'))
                        <div class="alert alert-success mb-6 flex items-start justify-between gap-4" data-flash>
                            <span>{{ session('status') }}</span>
                            <button type="button" class="text-forest/50 hover:text-forest" data-dismiss-flash aria-label="Tutup notifikasi">
                                <x-icon name="close" class="size-4" />
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>

            <footer class="border-t border-ink/10">
                <div class="page-shell flex flex-col gap-2 py-5 text-xs text-ink/45 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ date('Y') }} e-ARSIP · Sistem Arsip Sertifikat</p>
                    <p class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('verify.index') }}" class="hover:text-forest">Verifikasi publik</a>
                        <span class="text-ink/20">·</span>
                        <span>Semua aksi penting tercatat di audit log</span>
                    </p>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
