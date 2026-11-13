@extends('layouts.backoffice')

@section('title', 'Dashboard')

@section('content')
    <x-page-header eyebrow="Ruang kerja" title="Dashboard" subtitle="Ringkasan arsip, penerbitan, dan pekerjaan yang menunggu.">
        <a href="{{ route('verify.index') }}" class="btn btn-secondary btn-sm">
            <x-icon name="shield" class="size-4" />
            Verifikasi publik
        </a>
    </x-page-header>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['users', 'Peserta', $stats['participants'], 'Total peserta terdaftar'],
            ['layers', 'Angkatan', $stats['batches'], 'Program dan kelas pelatihan'],
            ['certificate', 'Sertifikat aktif', $stats['certificates'], 'Sertifikat yang masih berlaku'],
            ['reissue', 'Reissue menunggu', $stats['pending_reissues'], 'Permohonan belum diputuskan'],
        ] as $index => [$icon, $label, $value, $description])
            <article class="stat-card reveal reveal-{{ $index + 1 }}">
                <div class="flex items-start justify-between gap-3">
                    <p class="stat-label">{{ $label }}</p>
                    <span class="grid size-9 place-items-center rounded-xl border border-forest/15 bg-forest/5 text-forest">
                        <x-icon :name="$icon" class="size-4" />
                    </span>
                </div>
                <p class="stat-value">{{ number_format($value, 0, ',', '.') }}</p>
                <p class="stat-note">{{ $description }}</p>
            </article>
        @endforeach
    </section>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Manajemen pelatihan</h2>
                    <p class="section-subtitle">Kelola pendaftaran, absensi, nilai, dan penerbitan sertifikat.</p>
                </div>
                <span class="badge badge-neutral">{{ $stats['participants'] }} peserta</span>
            </div>

            <div class="divide-y divide-ink/10">
                @foreach ([
                    ['book', 'Program Pelatihan', 'Atur katalog program dan standar kelulusan.', route('admin.programs.index')],
                    ['layers', 'Angkatan & Kelas', 'Buat angkatan, atur jadwal, dan kelola pesertanya.', route('admin.batches.index')],
                    ['search', 'Pencarian Peserta', 'Filter cepat berdasarkan NIK, nama, tahun, dan kelas.', route('admin.participants.index')],
                    ['certificate', 'Sertifikat', 'Terbitkan PDF, kelola QR verifikasi, dan gambar tanda tangan.', route('admin.certificates.index')],
                    ['chart', 'Laporan Kelulusan', 'Rekap per program/periode dan ekspor Excel/PDF.', route('admin.reports.graduation')],
                    ['reissue', 'Penerbitan Ulang', 'Ajukan, setujui, dan terbitkan sertifikat pengganti.', route('admin.reissues.index')],
                ] as [$icon, $modul, $description, $url])
                    <a href="{{ $url }}" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-forest/[0.035] sm:px-6">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl border border-ink/10 bg-white text-ink/50 transition group-hover:border-forest/20 group-hover:text-forest">
                            <x-icon :name="$icon" class="size-5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block font-semibold text-ink">{{ $modul }}</span>
                            <span class="mt-0.5 block text-sm text-ink/50">{{ $description }}</span>
                        </span>
                        <x-icon name="arrow-right" class="ml-auto size-4 shrink-0 text-ink/25 transition group-hover:translate-x-0.5 group-hover:text-forest" />
                    </a>
                @endforeach
            </div>
        </section>

        <section class="space-y-4">
            <div class="card p-5">
                <h2 class="section-title">Pintasan</h2>
                <div class="mt-4 space-y-2">
                    <a href="{{ route('verify.index') }}" class="flex items-center gap-3 rounded-xl border border-ink/10 p-4 transition hover:border-forest/25 hover:bg-forest/[0.03]">
                        <span class="grid size-9 place-items-center rounded-lg bg-forest/5 text-forest"><x-icon name="shield" class="size-4" /></span>
                        <span>
                            <span class="block text-sm font-semibold text-ink">Verifikasi sertifikat</span>
                            <span class="mt-0.5 block text-xs text-ink/50">Cek nomor dan status legalitas.</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.participants.index') }}" class="flex items-center gap-3 rounded-xl border border-ink/10 p-4 transition hover:border-forest/25 hover:bg-forest/[0.03]">
                        <span class="grid size-9 place-items-center rounded-lg bg-forest/5 text-forest"><x-icon name="search" class="size-4" /></span>
                        <span>
                            <span class="block text-sm font-semibold text-ink">Cari peserta</span>
                            <span class="mt-0.5 block text-xs text-ink/50">Berdasarkan NIK, nama, tahun, atau kelas.</span>
                        </span>
                    </a>

                    @if (auth()->user()->role === \App\Enums\UserRole::Admin)
                        <a href="{{ route('admin.retention.index') }}" class="flex items-center gap-3 rounded-xl border border-ink/10 p-4 transition hover:border-forest/25 hover:bg-forest/[0.03]">
                            <span class="grid size-9 place-items-center rounded-lg bg-forest/5 text-forest"><x-icon name="archive" class="size-4" /></span>
                            <span>
                                <span class="block text-sm font-semibold text-ink">Retensi alumni</span>
                                <span class="mt-0.5 block text-xs text-ink/50">Kebijakan arsip dan pemulihan data.</span>
                            </span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 rounded-xl border border-ink/10 p-4 transition hover:border-forest/25 hover:bg-forest/[0.03]">
                            <span class="grid size-9 place-items-center rounded-lg bg-forest/5 text-forest"><x-icon name="users" class="size-4" /></span>
                            <span>
                                <span class="block text-sm font-semibold text-ink">Manajemen pengguna</span>
                                <span class="mt-0.5 block text-xs text-ink/50">Akun admin dan staf arsip.</span>
                            </span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-forest/15 bg-forest/[0.04] p-5">
                <div class="flex items-start gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full border border-forest/20 bg-white text-forest">
                        <x-icon name="shield" class="size-4" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-forest">Keamanan aktif</p>
                        <p class="mt-1 text-xs leading-5 text-ink/55">
                            Login dibatasi 5 percobaan per menit. Semua aksi penting dicatat di audit log.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
