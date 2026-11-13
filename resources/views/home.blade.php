@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
    @php
        $qrRows = [
            '111101111',
            '101101101',
            '111101111',
            '000101000',
            '111111111',
            '000101000',
            '111101111',
            '101101101',
            '111101111',
        ];
    @endphp

    <section class="page-shell grid items-center gap-14 py-14 lg:grid-cols-[1.08fr_0.92fr] lg:py-20">
        <div class="reveal">
            <p class="eyebrow">Sistem Arsip Sertifikat</p>
            <h1 class="display-xl mt-4 max-w-3xl text-balance">
                Arsip yang tertata.<br>
                Sertifikat yang dipercaya.
            </h1>
            <p class="mt-6 max-w-xl text-base leading-7 text-ink/60 sm:text-lg">
                e-ARSIP menyatukan data peserta, angkatan, nilai, penerbitan sertifikat, dan verifikasi publik.
                Setiap sertifikat punya nomor unik, jejak audit, dan halaman verifikasi yang bisa diperiksa siapa saja.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('verify.index') }}" class="btn btn-primary">
                    Verifikasi sertifikat
                    <x-icon name="arrow-right" class="size-4" />
                </a>
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-secondary">
                    {{ auth()->check() ? 'Buka dashboard' : 'Masuk staf' }}
                </a>
            </div>

            <dl class="mt-10 grid max-w-xl grid-cols-3 gap-4 border-t border-ink/10 pt-5">
                <div>
                    <dt class="text-[0.62rem] font-semibold tracking-[0.16em] text-ink/40 uppercase">Nomor</dt>
                    <dd class="mt-1 font-display text-lg font-semibold">Unik &amp; atomik</dd>
                </div>
                <div>
                    <dt class="text-[0.62rem] font-semibold tracking-[0.16em] text-ink/40 uppercase">Verifikasi</dt>
                    <dd class="mt-1 font-display text-lg font-semibold">Tanpa login</dd>
                </div>
                <div>
                    <dt class="text-[0.62rem] font-semibold tracking-[0.16em] text-ink/40 uppercase">Jejak</dt>
                    <dd class="mt-1 font-display text-lg font-semibold">Audit lengkap</dd>
                </div>
            </dl>
        </div>

        <div class="reveal reveal-2">
            <div class="certificate-specimen -rotate-1 transition-transform duration-500 hover:rotate-0">
                <div class="relative z-10">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[0.6rem] font-semibold tracking-[0.2em] text-brass uppercase">Sertifikat pelatihan</p>
                            <p class="mt-2 font-display text-xs text-ink/45">Lembaga Pelatihan · 2026</p>
                        </div>
                        <span class="seal seal-lg">eA</span>
                    </div>

                    <div class="mt-10 text-center">
                        <p class="font-display text-[0.7rem] tracking-[0.3em] text-ink/40 uppercase">Diberikan kepada</p>
                        <p class="mt-3 font-display text-3xl font-semibold tracking-[-0.02em] text-ink sm:text-4xl">Budi Santoso</p>
                        <p class="mt-3 text-sm text-ink/55">atas keberhasilan menyelesaikan</p>
                        <p class="mt-1 font-display text-lg font-semibold text-forest">Pelatihan Kepemimpinan Dasar</p>
                    </div>

                    <div class="mt-10 flex items-end justify-between gap-6 border-t border-brass/30 pt-5">
                        <div>
                            <p class="text-[0.58rem] font-semibold tracking-[0.16em] text-ink/35 uppercase">Nomor sertifikat</p>
                            <p class="mt-1 font-mono text-sm font-medium text-ink">PEL/2026/0001</p>
                            <p class="mt-3 text-[0.58rem] font-semibold tracking-[0.16em] text-ink/35 uppercase">Diterbitkan</p>
                            <p class="mt-1 text-xs text-ink/60">10 Februari 2026</p>
                        </div>
                        <div class="text-right">
                            <div class="grid grid-cols-9 gap-[2px] rounded-md border border-ink/10 bg-white p-2" aria-hidden="true">
                                @foreach ($qrRows as $row)
                                    @foreach (str_split($row) as $cell)
                                        <span @class(['size-[5px] rounded-[1px]', 'bg-ink' => $cell === '1', 'bg-transparent' => $cell !== '1'])></span>
                                    @endforeach
                                @endforeach
                            </div>
                            <p class="mt-2 text-[0.55rem] tracking-[0.12em] text-ink/35 uppercase">Pindai untuk verifikasi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-ink/10 bg-paper">
        <div class="page-shell py-14">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Alur arsip</p>
                    <h2 class="display-lg mt-2">Dari data mentah menjadi sertifikat yang bisa diperiksa.</h2>
                </div>
                <p class="max-w-sm text-sm leading-6 text-ink/55">
                    Lima langkah yang membentuk siklus hidup satu sertifikat, dari pendaftaran sampai verifikasi publik.
                </p>
            </div>

            <ol class="mt-10 grid gap-px overflow-hidden rounded-2xl border border-ink/10 bg-ink/10 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    ['01', 'Data peserta', 'Biodata, angkatan, dan status pendaftaran tercatat rapi.'],
                    ['02', 'Presensi & nilai', 'Kehadiran dan nilai akhir terhubung per kelas.'],
                    ['03', 'Penerbitan', 'Nomor unik, PDF, QR, dan hash dibuat otomatis.'],
                    ['04', 'Verifikasi', 'Publik memeriksa keaslian tanpa melihat data sensitif.'],
                    ['05', 'Jejak audit', 'Setiap keputusan dan penerbitan ulang tercatat.'],
                ] as [$number, $title, $description])
                    <li class="bg-paper p-5">
                        <span class="font-mono text-[0.68rem] font-medium text-brass">{{ $number }}</span>
                        <h3 class="mt-4 font-display text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-ink/55">{{ $description }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="page-shell py-16">
        <div class="ledger-rule"><span>Yang dijaga sistem</span></div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['users', 'Data peserta', 'Biodata, status pendaftaran, presensi, dan rekap nilai per angkatan.'],
                ['certificate', 'Sertifikat unik', 'Nomor anti-duplikat, PDF otomatis, QR code, dan gambar tanda tangan.'],
                ['shield', 'Verifikasi publik', 'Pengecekan keaslian oleh HR tanpa login dan tanpa data sensitif.'],
                ['archive', 'Jejak audit', 'Penerbitan ulang, alasan, pelaksana, dan timestamp tercatat.'],
            ] as [$icon, $title, $description])
                <article class="card p-5 transition duration-300 hover:-translate-y-0.5 hover:border-forest/20">
                    <span class="grid size-10 place-items-center rounded-xl border border-forest/15 bg-forest/5 text-forest">
                        <x-icon :name="$icon" class="size-5" />
                    </span>
                    <h2 class="mt-5 font-display text-lg font-semibold">{{ $title }}</h2>
                    <p class="mt-2 text-sm leading-6 text-ink/55">{{ $description }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="page-shell pb-16">
        <div class="overflow-hidden rounded-[2rem] bg-forest-deep px-6 py-10 text-paper sm:px-10 lg:flex lg:items-center lg:justify-between lg:gap-10">
            <div class="max-w-2xl">
                <p class="text-[0.66rem] font-semibold tracking-[0.2em] text-brass uppercase">Untuk HR &amp; pihak luar</p>
                <h2 class="mt-3 font-display text-3xl font-semibold tracking-[-0.03em] text-white sm:text-4xl">
                    Periksa keaslian sertifikat dalam hitungan detik.
                </h2>
                <p class="mt-3 text-sm leading-6 text-paper/60">
                    Masukkan nomor sertifikat atau pindai QR pada dokumen. Hasil verifikasi hanya menampilkan informasi yang aman untuk publik.
                </p>
            </div>
            <a href="{{ route('verify.index') }}" class="btn mt-6 bg-brass text-forest-deep hover:bg-[#c79a5a] lg:mt-0">
                Buka halaman verifikasi
                <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>
    </section>
@endsection
