@extends('layouts.app')

@section('title', 'Detail Penerbitan Ulang')

@section('content')
    @php
        $badge = match ($reissue->status) {
            \App\Enums\ReissueStatus::Diajukan => 'bg-amber-100 text-amber-800',
            \App\Enums\ReissueStatus::Disetujui => 'bg-sky-100 text-sky-800',
            \App\Enums\ReissueStatus::Ditolak => 'bg-red-100 text-red-800',
            \App\Enums\ReissueStatus::Selesai => 'bg-emerald-100 text-emerald-800',
        };
        $isAdmin = auth()->user()->role === \App\Enums\UserRole::Admin;
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500">Permohonan reissue</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-slate-900">{{ $reissue->originalCertificate?->nomor_sertifikat }}</h1>
            <span class="mt-2 inline-block rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">{{ ucfirst($reissue->status->value) }}</span>
        </div>
        <a href="{{ route('admin.reissues.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Informasi permohonan</h2>

            <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-slate-500">Sertifikat asal</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $reissue->originalCertificate?->nomor_sertifikat ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Status sertifikat asal</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $reissue->originalCertificate?->status?->value ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Peserta</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $reissue->originalCertificate?->enrollment?->participant?->nama_lengkap ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Program</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $reissue->originalCertificate?->enrollment?->batch?->program?->nama ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Pemohon</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $reissue->pemohon_nama ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Kontak pemohon</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $reissue->pemohon_kontak ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">Alasan</dt>
                    <dd class="mt-0.5 whitespace-pre-line font-medium text-slate-900">{{ $reissue->alasan }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Diajukan oleh</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $reissue->diprosesOleh?->nama ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Waktu pengajuan</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $reissue->created_at?->format('d-m-Y H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Disetujui oleh</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $reissue->disetujuiOleh?->nama ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Waktu keputusan</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $reissue->decided_at?->format('d-m-Y H:i') ?? '—' }}</dd>
                </div>
                @if ($reissue->catatan_keputusan)
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Catatan keputusan</dt>
                        <dd class="mt-0.5 whitespace-pre-line font-medium text-slate-900">{{ $reissue->catatan_keputusan }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="space-y-4">
            @if ($reissue->newCertificate)
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Sertifikat pengganti</h2>
                    <p class="mt-2 text-lg font-semibold text-emerald-900">{{ $reissue->newCertificate->nomor_sertifikat }}</p>
                    <p class="text-sm text-emerald-800">Terbit {{ $reissue->newCertificate->tanggal_terbit?->format('d-m-Y') }}</p>
                    @if ($reissue->newCertificate->file_path)
                        <a href="{{ route('admin.certificates.download', $reissue->newCertificate) }}" class="mt-4 inline-block rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800">
                            Unduh PDF pengganti
                        </a>
                    @endif
                </div>
            @endif

            @if ($isAdmin && $reissue->status === \App\Enums\ReissueStatus::Diajukan)
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Keputusan admin</h2>

                    <form method="POST" action="{{ route('admin.reissues.approve', $reissue) }}" class="mt-4 space-y-3">
                        @csrf
                        <label class="block text-sm font-medium">Catatan persetujuan (opsional)
                            <textarea name="catatan_keputusan" rows="2" maxlength="2000" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm"></textarea>
                        </label>
                        <button type="submit" class="w-full rounded-lg bg-emerald-700 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">
                            Setujui
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.reissues.reject', $reissue) }}" class="mt-5 space-y-3 border-t border-slate-200 pt-5">
                        @csrf
                        <label class="block text-sm font-medium">Alasan penolakan
                            <textarea name="catatan_keputusan" rows="2" required maxlength="2000" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm"></textarea>
                        </label>
                        <button type="submit" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50">
                            Tolak
                        </button>
                    </form>
                </div>
            @endif

            @if ($reissue->status === \App\Enums\ReissueStatus::Disetujui)
                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-sky-700">Terbitkan pengganti</h2>
                    <p class="mt-2 text-sm text-sky-800">Sertifikat lama akan berstatus <strong>diganti</strong> dan sertifikat baru diterbitkan dengan nomor baru.</p>
                    <form method="POST" action="{{ route('admin.reissues.process', $reissue) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-sky-700 px-3 py-2 text-sm font-semibold text-white transition hover:bg-sky-800">
                            Proses penerbitan ulang
                        </button>
                    </form>
                </div>
            @endif

            @if ($reissue->status === \App\Enums\ReissueStatus::Ditolak)
                <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">
                    Permohonan ditolak. Lihat catatan keputusan di samping.
                </div>
            @endif
        </section>
    </div>
@endsection
