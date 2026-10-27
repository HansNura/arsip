@extends('layouts.app')

@section('title', 'Verifikasi Sertifikat')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="text-center">
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Verifikasi Sertifikat</h1>
            <p class="mt-3 text-slate-600">
                Masukkan nomor sertifikat untuk memeriksa keasliannya. Verifikasi tidak memerlukan login.
            </p>
        </div>

        <form method="POST" action="{{ route('verify.check') }}" class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @csrf
            <label for="nomor_sertifikat" class="block text-sm font-medium text-slate-700">Nomor sertifikat</label>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                <input id="nomor_sertifikat" name="nomor_sertifikat" type="text" value="{{ old('nomor_sertifikat', $nomor ?? '') }}" required
                       placeholder="Contoh: PEL/2026/0001"
                       class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                <button type="submit" class="shrink-0 rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                    Verifikasi
                </button>
            </div>
            @error('nomor_sertifikat')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>

        @isset($result)
            <div class="mt-6 rounded-2xl border p-6
                @if ($result === \App\Enums\VerificationResult::Valid)
                    border-emerald-200 bg-emerald-50
                @elseif ($result === \App\Enums\VerificationResult::Dibatalkan)
                    border-red-200 bg-red-50
                @else
                    border-amber-200 bg-amber-50
                @endif">
                @if ($result === \App\Enums\VerificationResult::Valid)
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Status validitas</p>
                    <p class="mt-1 text-lg font-semibold text-emerald-800">Valid</p>
                @elseif ($result === \App\Enums\VerificationResult::Dibatalkan)
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Status validitas</p>
                    <p class="mt-1 text-lg font-semibold text-red-800">Dibatalkan</p>
                @else
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Status validitas</p>
                    <p class="mt-1 text-lg font-semibold text-amber-800">Tidak Ditemukan</p>
                @endif

                @if ($certificate)
                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-500">Nomor sertifikat</dt>
                            <dd class="mt-0.5 font-medium text-slate-900">{{ $certificate->nomor_sertifikat }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Status sertifikat</dt>
                            <dd class="mt-0.5 font-medium text-slate-900">{{ ucfirst($certificate->status) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Nama</dt>
                            <dd class="mt-0.5 font-medium text-slate-900">{{ $certificate->nama_lengkap }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Program</dt>
                            <dd class="mt-0.5 font-medium text-slate-900">{{ $certificate->program }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Angkatan</dt>
                            <dd class="mt-0.5 font-medium text-slate-900">{{ $certificate->kode_batch }} ({{ $certificate->tahun }})</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tanggal terbit</dt>
                            <dd class="mt-0.5 font-medium text-slate-900">{{ \Illuminate\Support\Carbon::parse($certificate->tanggal_terbit)->translatedFormat('d F Y') }}</dd>
                        </div>
                    </dl>

                    @if ($result === \App\Enums\VerificationResult::Dibatalkan)
                        <p class="mt-4 text-sm text-red-700">
                            Sertifikat ini tercatat namun sudah dibatalkan atau digantikan. Hubungi instansi penerbit untuk informasi lanjutan.
                        </p>
                    @endif
                @else
                    <p class="mt-3 text-sm text-amber-800">
                        Nomor <span class="font-mono">{{ $nomor }}</span> tidak ada dalam arsip. Periksa kembali penulisan nomor sertifikat.
                    </p>
                @endif
            </div>
        @endisset

        <p class="mt-6 text-center text-xs text-slate-500">
            Demi privasi, halaman ini hanya menampilkan nama, program, angkatan, dan tanggal terbit.
            NIK, alamat, dan kontak tidak pernah ditampilkan.
        </p>
    </div>
@endsection
