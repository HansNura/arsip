@extends('layouts.backoffice')

@section('title', 'Detail Penerbitan Ulang')

@section('content')
    @php
        $isAdmin = auth()->user()->role === \App\Enums\UserRole::Admin;
        $badge = match ($reissue->status) {
            \App\Enums\ReissueStatus::Diajukan => 'badge-pending',
            \App\Enums\ReissueStatus::Disetujui => 'badge-info',
            \App\Enums\ReissueStatus::Ditolak => 'badge-cancelled',
            \App\Enums\ReissueStatus::Selesai => 'badge-valid',
        };
        $stepStates = match ($reissue->status) {
            \App\Enums\ReissueStatus::Diajukan => ['current', 'upcoming', 'upcoming'],
            \App\Enums\ReissueStatus::Disetujui => ['done', 'current', 'upcoming'],
            \App\Enums\ReissueStatus::Selesai => ['done', 'done', 'done'],
            \App\Enums\ReissueStatus::Ditolak => ['done', 'rejected', 'upcoming'],
        };
        $steps = ['Diajukan', 'Disetujui', 'Selesai'];
    @endphp

    <x-page-header eyebrow="Permohonan reissue" :title="$reissue->originalCertificate?->nomor_sertifikat ?? 'Detail reissue'"
                  subtitle="Riwayat keputusan dan penerbitan sertifikat pengganti.">
        <span class="badge {{ $badge }}">{{ ucfirst($reissue->status->value) }}</span>
        <a href="{{ route('admin.reissues.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger mt-6">{{ $errors->first() }}</div>
    @endif

    <ol class="card mt-6 grid gap-4 p-5 sm:grid-cols-3 sm:p-6">
        @foreach ($steps as $index => $label)
            @php
                $state = $stepStates[$index];
                $circle = match ($state) {
                    'done' => 'bg-forest text-white border-forest',
                    'current' => 'bg-forest/5 text-forest border-forest/30',
                    'rejected' => 'bg-clay text-white border-clay',
                    default => 'bg-white text-ink/30 border-ink/15',
                };
                $line = match ($state) {
                    'done' => 'bg-forest/30',
                    'rejected' => 'bg-clay/30',
                    default => 'bg-ink/10',
                };
                $labelClass = match ($state) {
                    'done', 'current' => 'text-ink',
                    'rejected' => 'text-clay',
                    default => 'text-ink/35',
                };
            @endphp
            <li>
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full border text-xs font-bold {{ $circle }}">
                        @if ($state === 'done')
                            <x-icon name="check" class="size-4" />
                        @elseif ($state === 'rejected')
                            <x-icon name="warning" class="size-4" />
                        @else
                            {{ $index + 1 }}
                        @endif
                    </span>
                    @if ($index < 2)
                        <span class="hidden h-px flex-1 sm:block {{ $line }}"></span>
                    @endif
                </div>
                <p class="mt-2 text-sm font-semibold {{ $labelClass }}">{{ $label }}</p>
            </li>
        @endforeach
    </ol>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Informasi permohonan</h2>
                    <p class="section-subtitle">Detail permohonan dan jejak keputusan.</p>
                </div>
            </div>
            <dl class="grid gap-x-6 gap-y-5 px-5 py-6 text-sm sm:grid-cols-2 sm:px-6">
                @foreach ([
                    ['Sertifikat asal', $reissue->originalCertificate?->nomor_sertifikat ?? '—'],
                    ['Status sertifikat asal', $reissue->originalCertificate?->status?->value ?? '—'],
                    ['Peserta', $reissue->originalCertificate?->enrollment?->participant?->nama_lengkap ?? '—'],
                    ['Program', $reissue->originalCertificate?->enrollment?->batch?->program?->nama ?? '—'],
                    ['Pemohon', $reissue->pemohon_nama ?? '—'],
                    ['Kontak pemohon', $reissue->pemohon_kontak ?? '—'],
                    ['Diajukan oleh', $reissue->diprosesOleh?->nama ?? '—'],
                    ['Waktu pengajuan', $reissue->created_at?->format('d-m-Y H:i') ?? '—'],
                    ['Disetujui oleh', $reissue->disetujuiOleh?->nama ?? '—'],
                    ['Waktu keputusan', $reissue->decided_at?->format('d-m-Y H:i') ?? '—'],
                ] as [$label, $value])
                    <div>
                        <dt class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">{{ $label }}</dt>
                        <dd class="mt-1 font-medium text-ink">{{ $value }}</dd>
                    </div>
                @endforeach
                <div class="sm:col-span-2">
                    <dt class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Alasan</dt>
                    <dd class="mt-1 whitespace-pre-line font-medium text-ink">{{ $reissue->alasan }}</dd>
                </div>
                @if ($reissue->catatan_keputusan)
                    <div class="sm:col-span-2">
                        <dt class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Catatan keputusan</dt>
                        <dd class="mt-1 whitespace-pre-line font-medium text-ink">{{ $reissue->catatan_keputusan }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="space-y-4">
            @if ($reissue->newCertificate)
                <div class="rounded-2xl border border-forest/20 bg-forest/[0.045] p-5">
                    <p class="text-[0.62rem] font-semibold tracking-[0.14em] text-forest uppercase">Sertifikat pengganti</p>
                    <p class="mt-2 font-mono text-lg font-semibold text-ink">{{ $reissue->newCertificate->nomor_sertifikat }}</p>
                    <p class="mt-1 text-sm text-ink/55">Terbit {{ $reissue->newCertificate->tanggal_terbit?->format('d-m-Y') }}</p>
                    @if ($reissue->newCertificate->file_path)
                        <a href="{{ route('admin.certificates.download', $reissue->newCertificate) }}" class="btn btn-primary btn-sm mt-4">
                            <x-icon name="download" class="size-4" />
                            Unduh PDF pengganti
                        </a>
                    @endif
                </div>
            @endif

            @if ($isAdmin && $reissue->status === \App\Enums\ReissueStatus::Diajukan)
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Keputusan admin</h2>
                            <p class="section-subtitle">Penyetuju harus berbeda dari pengaju.</p>
                        </div>
                    </div>
                    <div class="card-body space-y-5">
                        <form method="POST" action="{{ route('admin.reissues.approve', $reissue) }}" class="space-y-3">
                            @csrf
                            <label class="block">
                                <span class="field-label">Catatan persetujuan (opsional)</span>
                                <textarea name="catatan_keputusan" rows="2" maxlength="2000" class="field-textarea"></textarea>
                            </label>
                            <button type="submit" class="btn btn-primary w-full">Setujui</button>
                        </form>

                        <form method="POST" action="{{ route('admin.reissues.reject', $reissue) }}" class="space-y-3 border-t border-ink/10 pt-5">
                            @csrf
                            <label class="block">
                                <span class="field-label">Alasan penolakan</span>
                                <textarea name="catatan_keputusan" rows="2" required maxlength="2000" class="field-textarea"></textarea>
                            </label>
                            <button type="submit" class="btn btn-danger w-full">Tolak</button>
                        </form>
                    </div>
                </div>
            @endif

            @if ($reissue->status === \App\Enums\ReissueStatus::Disetujui)
                <div class="rounded-2xl border border-sky-ink/20 bg-sky-ink/[0.045] p-5">
                    <p class="text-[0.62rem] font-semibold tracking-[0.14em] text-sky-ink uppercase">Terbitkan pengganti</p>
                    <p class="mt-2 text-sm leading-6 text-ink/60">Sertifikat lama akan berstatus <strong>diganti</strong> dan sertifikat baru diterbitkan dengan nomor baru.</p>
                    <form method="POST" action="{{ route('admin.reissues.process', $reissue) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">Proses penerbitan ulang</button>
                    </form>
                </div>
            @endif

            @if ($reissue->status === \App\Enums\ReissueStatus::Ditolak)
                <div class="alert alert-danger">Permohonan ditolak. Lihat catatan keputusan di samping.</div>
            @endif
        </section>
    </div>
@endsection
