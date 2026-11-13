@extends('layouts.backoffice')

@section('title', 'Sertifikat')

@section('content')
    <x-page-header eyebrow="Sertifikasi" title="Sertifikat" subtitle="Terbitkan, pantau, dan unduh sertifikat peserta yang lulus.">
        <a href="{{ route('admin.certificate-templates.edit') }}" class="btn btn-secondary btn-sm">
            <x-icon name="settings" class="size-4" />
            Template &amp; tanda tangan
        </a>
    </x-page-header>

    @if ($errors->has('certificate') || $errors->has('enrollment') || $errors->has('template'))
        <div class="alert alert-danger mt-6">
            {{ $errors->first('certificate') ?: $errors->first('enrollment') ?: $errors->first('template') }}
        </div>
    @endif

    <section class="card mt-6">
        <div class="card-header">
            <div>
                <h2 class="section-title">Terbitkan per angkatan</h2>
                <p class="section-subtitle">Hanya peserta lulus dan belum memiliki sertifikat aktif yang diproses.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.certificates.store-batch-request') }}" class="card-body flex flex-wrap items-end gap-3">
            @csrf
            <label class="block min-w-72 flex-1">
                <span class="field-label">Angkatan</span>
                <select name="batch_id" required class="field-select">
                    <option value="">Pilih angkatan</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->id }}" @selected(($filters['batch_id'] ?? '') == $batch->id)>
                            {{ $batch->program->nama }} · {{ $batch->nama }} · {{ $batch->tahun }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn btn-primary">
                <x-icon name="certificate" class="size-4" />
                Terbitkan semua yang lulus
            </button>
        </form>
    </section>

    <form method="GET" action="{{ route('admin.certificates.index') }}" class="card mt-6 p-5">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="block">
                <span class="field-label">Cari nomor atau nama</span>
                <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="PEL/2026/0001 atau nama peserta" class="field-input">
            </label>
            <label class="block">
                <span class="field-label">Status</span>
                <select name="status" class="field-select">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst($status->value) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="field-label">Angkatan</span>
                <select name="batch_id" class="field-select">
                    <option value="">Semua angkatan</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->id }}" @selected(($filters['batch_id'] ?? '') == $batch->id)>{{ $batch->nama }} · {{ $batch->tahun }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="{{ route('admin.certificates.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            </div>
        </div>
    </form>

    @if ($selectedBatch)
        <section class="card mt-6">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Peserta lulus belum bersertifikat</h2>
                    <p class="section-subtitle">{{ $selectedBatch->nama }} · {{ $eligibleEnrollments->count() }} peserta menunggu penerbitan.</p>
                </div>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Peserta</th>
                            <th>Nomor peserta</th>
                            <th>Nilai akhir</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($eligibleEnrollments as $enrollment)
                            <tr>
                                <td><span class="font-semibold text-ink">{{ $enrollment->participant->nama_lengkap }}</span></td>
                                <td class="font-mono text-xs">{{ $enrollment->nomor_peserta ?? '—' }}</td>
                                <td class="font-mono text-xs">{{ $enrollment->nilai_akhir ?? '—' }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.certificates.store', $enrollment) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm">Terbitkan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="table-empty">Tidak ada peserta lulus yang menunggu penerbitan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <div class="table-shell mt-6">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Peserta</th>
                        <th>Program</th>
                        <th>Angkatan</th>
                        <th>Status</th>
                        <th>Terbit</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($certificates as $certificate)
                        <tr>
                            <td><span class="font-mono text-xs font-medium text-ink">{{ $certificate->nomor_sertifikat }}</span></td>
                            <td>{{ $certificate->enrollment?->participant?->nama_lengkap ?? '—' }}</td>
                            <td>{{ $certificate->enrollment?->batch?->program?->nama ?? '—' }}</td>
                            <td>
                                {{ $certificate->enrollment?->batch?->nama ?? '—' }}
                                @if ($certificate->enrollment?->batch?->tahun)
                                    <span class="text-xs text-ink/35">· {{ $certificate->enrollment->batch->tahun }}</span>
                                @endif
                            </td>
                            <td>
                                <span @class(['badge', 'badge-valid' => $certificate->status === \App\Enums\CertificateStatus::Aktif, 'badge-neutral' => $certificate->status !== \App\Enums\CertificateStatus::Aktif])>
                                    {{ ucfirst($certificate->status->value) }}
                                </span>
                            </td>
                            <td class="font-mono text-xs">{{ $certificate->tanggal_terbit?->format('d-m-Y') ?? '—' }}</td>
                            <td class="text-right">
                                @if ($certificate->file_path)
                                    <a href="{{ route('admin.certificates.download', $certificate) }}" class="btn btn-secondary btn-sm">
                                        <x-icon name="download" class="size-3.5" />
                                        Unduh PDF
                                    </a>
                                @else
                                    <span class="text-xs text-ink/35">Belum ada berkas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">
                                <x-empty-state icon="certificate" title="Belum ada sertifikat" description="Terbitkan sertifikat untuk peserta yang sudah lulus." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($certificates->hasPages())
            <div class="border-t border-ink/10 px-5 py-4">{{ $certificates->links() }}</div>
        @endif
    </div>
@endsection
