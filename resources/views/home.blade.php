@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <section class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr]">
        <div>
            <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-800">
                Sistem Arsip Sertifikat
            </span>

            <h1 class="mt-5 text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">
                Arsip pelatihan yang rapi, sertifikat yang dapat dipercaya.
            </h1>

            <p class="mt-5 max-w-2xl text-lg leading-relaxed text-slate-600">
                e-ARSIP mengelola data peserta, angkatan, nilai, dan sertifikat dalam satu tempat.
                Setiap sertifikat memiliki nomor unik dan dapat diverifikasi publik secara aman.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('verify.index') }}" class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                    Verifikasi Sertifikat
                </a>
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                    {{ auth()->check() ? 'Buka Dashboard' : 'Masuk Staf' }}
                </a>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <p class="text-sm font-semibold text-slate-900">Alur kerja arsip</p>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">FR-01 s/d FR-06</span>
            </div>

            <ol class="mt-5 space-y-4 text-sm">
                @foreach ([
                    'Data peserta dan angkatan tercatat rapi.',
                    'Nilai dan presensi terhubung per kelas.',
                    'Sertifikat diterbitkan dengan nomor unik.',
                    'Publik memverifikasi keaslian tanpa melihat data sensitif.',
                    'Penerbitan ulang tercatat lengkap di jejak audit.',
                ] as $step)
                    <li class="flex gap-3">
                        <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-emerald-100 text-[11px] font-bold text-emerald-800">
                            {{ $loop->iteration }}
                        </span>
                        <span class="text-slate-600">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="mt-16 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Data Peserta', 'Biodata, status pendaftaran, presensi, dan rekap nilai per angkatan.'],
            ['Sertifikat Unik', 'Nomor anti-duplikat, PDF otomatis, QR code, dan tanda tangan digital.'],
            ['Verifikasi Publik', 'Pengecekan keaslian oleh HR tanpa login dan tanpa data sensitif.'],
            ['Jejak Audit', 'Pencatatan penerbitan ulang, alasan, pelaksana, dan timestamp.'],
        ] as [$title, $description])
            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $description }}</p>
            </article>
        @endforeach
    </section>
@endsection
