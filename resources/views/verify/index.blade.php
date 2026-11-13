@extends('layouts.public')

@section('title', 'Verifikasi Sertifikat')

@section('content')
    <section class="page-shell py-12 lg:py-16">
        <div class="mx-auto max-w-4xl">
            <div class="text-center">
                <p class="eyebrow">Verifikasi publik</p>
                <h1 class="page-title mt-3">Periksa keaslian sertifikat.</h1>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-ink/55">
                    Masukkan nomor sertifikat atau buka tautan QR dari dokumen. Verifikasi tidak memerlukan login.
                </p>
            </div>

            <form method="POST" action="{{ route('verify.check') }}" class="card mx-auto mt-8 max-w-2xl p-5 sm:p-6">
                @csrf
                <label for="nomor_sertifikat" class="field-label">Nomor sertifikat</label>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                    <input id="nomor_sertifikat" name="nomor_sertifikat" type="text" value="{{ old('nomor_sertifikat', $nomor ?? '') }}" required
                           placeholder="Contoh: PEL/2026/0001" autocomplete="off"
                           class="field-input mt-0 flex-1 font-mono">
                    <button type="submit" class="btn btn-primary shrink-0">
                        Verifikasi
                        <x-icon name="arrow-right" class="size-4" />
                    </button>
                </div>
                @error('nomor_sertifikat')
                    <p class="field-error">{{ $message }}</p>
                @enderror
                <p class="field-hint">Nomor tercetak pada sertifikat, biasanya berformat PEL/2026/0001.</p>
            </form>

            @isset($result)
                @php
                    $isValid = $result === \App\Enums\VerificationResult::Valid;
                    $isCancelled = $result === \App\Enums\VerificationResult::Dibatalkan;
                    $panelClass = $isValid ? 'border-forest/25 bg-forest/[0.045]' : ($isCancelled ? 'border-clay/25 bg-clay/[0.045]' : 'border-amber-ink/25 bg-amber-ink/[0.045]');
                    $iconClass = $isValid ? 'bg-forest text-white' : ($isCancelled ? 'bg-clay text-white' : 'bg-amber-ink text-white');
                    $iconName = $isValid ? 'check' : ($isCancelled ? 'warning' : 'search');
                @endphp

                <div class="mx-auto mt-6 max-w-2xl overflow-hidden rounded-2xl border {{ $panelClass }}">
                    <div class="flex flex-wrap items-center gap-4 border-b border-ink/10 px-5 py-5 sm:px-6">
                        <span class="grid size-11 place-items-center rounded-full {{ $iconClass }}">
                            <x-icon :name="$iconName" class="size-5" />
                        </span>
                        <div>
                            <p class="text-[0.64rem] font-semibold tracking-[0.16em] text-ink/45 uppercase">Status validitas</p>
                            <p class="mt-1 font-display text-2xl font-semibold tracking-[-0.02em] text-ink">
                                @if ($isValid)
                                    Valid
                                @elseif ($isCancelled)
                                    Dibatalkan
                                @else
                                    Tidak Ditemukan
                                @endif
                            </p>
                        </div>
                        <span @class(['badge ml-auto', 'badge-valid' => $isValid, 'badge-cancelled' => $isCancelled, 'badge-pending' => ! $isValid && ! $isCancelled])>
                            {{ $isValid ? 'Sertifikat aktif' : ($isCancelled ? 'Tidak berlaku' : 'Tidak ada arsip') }}
                        </span>
                    </div>

                    @if ($certificate)
                        <dl class="grid gap-x-6 gap-y-5 px-5 py-6 text-sm sm:grid-cols-2 sm:px-6">
                            <div>
                                <dt class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Nomor sertifikat</dt>
                                <dd class="mt-1 font-mono font-medium text-ink">{{ $certificate->nomor_sertifikat }}</dd>
                            </div>
                            <div>
                                <dt class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Status sertifikat</dt>
                                <dd class="mt-1 font-medium text-ink">{{ ucfirst($certificate->status) }}</dd>
                            </div>
                            <div>
                                <dt class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Nama</dt>
                                <dd class="mt-1 font-medium text-ink">{{ $certificate->nama_lengkap }}</dd>
                            </div>
                            <div>
                                <dt class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Program</dt>
                                <dd class="mt-1 font-medium text-ink">{{ $certificate->program }}</dd>
                            </div>
                            <div>
                                <dt class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Angkatan</dt>
                                <dd class="mt-1 font-medium text-ink">{{ $certificate->kode_batch }} ({{ $certificate->tahun }})</dd>
                            </div>
                            <div>
                                <dt class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Tanggal terbit</dt>
                                <dd class="mt-1 font-medium text-ink">{{ \Illuminate\Support\Carbon::parse($certificate->tanggal_terbit)->translatedFormat('d F Y') }}</dd>
                            </div>
                        </dl>

                        @if ($isCancelled)
                            <p class="border-t border-ink/10 px-5 py-4 text-sm leading-6 text-clay sm:px-6">
                                Sertifikat ini tercatat namun sudah dibatalkan atau digantikan. Hubungi instansi penerbit untuk informasi lanjutan.
                            </p>
                        @endif
                    @else
                        <div class="px-5 py-6 text-sm leading-6 text-ink/60 sm:px-6">
                            @if ($viaQr ?? false)
                                Tautan verifikasi tidak dikenali. Periksa kembali tautan atau pindai ulang QR code pada sertifikat.
                            @else
                                Nomor <span class="font-mono font-medium text-ink">{{ $nomor }}</span> tidak ada dalam arsip. Periksa kembali penulisan nomor sertifikat.
                            @endif
                        </div>
                    @endif
                </div>
            @endisset

            <div class="mx-auto mt-8 grid max-w-2xl gap-4 sm:grid-cols-2">
                <div class="card-muted p-5">
                    <p class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Yang ditampilkan</p>
                    <p class="mt-2 text-sm leading-6 text-ink/60">Nama, program, angkatan, nomor, dan tanggal terbit.</p>
                </div>
                <div class="card-muted p-5">
                    <p class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Yang tidak ditampilkan</p>
                    <p class="mt-2 text-sm leading-6 text-ink/60">NIK, alamat, email, dan nomor telepon tidak pernah dibuka ke publik.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
