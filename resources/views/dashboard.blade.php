@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Dashboard</h1>
            <p class="mt-2 text-sm text-slate-600">
                Selamat datang, <span class="font-medium text-slate-900">{{ auth()->user()->nama }}</span>.
                Peran Anda: <span class="font-medium text-slate-900">{{ str_replace('_', ' ', auth()->user()->role->value) }}</span>.
            </p>
        </div>
        <a href="{{ route('verify.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
            Buka Verifikasi Publik
        </a>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Peserta', $stats['participants'], 'Total peserta terdaftar'],
            ['Angkatan', $stats['batches'], 'Program dan kelas pelatihan'],
            ['Sertifikat Aktif', $stats['certificates'], 'Sertifikat yang masih berlaku'],
            ['Reissue Menunggu', $stats['pending_reissues'], 'Permohonan belum selesai'],
        ] as [$label, $value, $description])
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($value, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">Modul yang siap dikembangkan</h2>
            <p class="mt-1 text-sm text-slate-600">Fondasi database, autentikasi, dan RBAC sudah aktif.</p>

            <ul class="mt-5 divide-y divide-slate-100 text-sm">
                @foreach ([
                    ['Data Peserta', 'CRUD peserta, angkatan, presensi, dan nilai.', 'Sprint 2'],
                    ['Sertifikasi', 'Generator nomor unik, PDF, QR, dan gambar tanda tangan.', 'Sprint 3'],
                    ['Verifikasi Publik', 'Halaman publik sudah tersedia dan mencatat log.', 'Sprint 4'],
                    ['Pelaporan & Retensi', 'Rekap kelulusan dan kebijakan arsip.', 'Sprint 5'],
                    ['Audit & Reissue', 'Penerbitan ulang dan jejak audit.', 'Sprint 6'],
                ] as [$modul, $description, $sprint])
                    <li class="flex items-start justify-between gap-4 py-3">
                        <div>
                            <p class="font-medium text-slate-900">{{ $modul }}</p>
                            <p class="mt-0.5 text-slate-600">{{ $description }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ $sprint }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">Pintasan</h2>
            <div class="mt-4 space-y-3">
                <a href="{{ route('verify.index') }}" class="block rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                    <p class="text-sm font-semibold text-slate-900">Verifikasi sertifikat</p>
                    <p class="mt-1 text-sm text-slate-600">Cek nomor sertifikat dan status legalitasnya.</p>
                </a>

                @if (auth()->user()->role === \App\Enums\UserRole::Admin)
                    <a href="{{ route('admin.users.index') }}" class="block rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                        <p class="text-sm font-semibold text-slate-900">Manajemen pengguna</p>
                        <p class="mt-1 text-sm text-slate-600">Lihat akun admin dan staf arsip.</p>
                    </a>
                @endif
            </div>

            <div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-medium text-slate-700">Keamanan</p>
                <p class="mt-1">Login dibatasi 5 percobaan per menit. Semua aksi penting dicatat di jejak audit.</p>
            </div>
        </section>
    </div>
@endsection
