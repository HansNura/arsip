@extends('layouts.backoffice')

@section('title', 'Pencarian Peserta')

@section('content')
    <x-page-header eyebrow="Data peserta" title="Pencarian Peserta" subtitle="Cari peserta berdasarkan NIK, nama, tahun angkatan, atau kelas." />

    <form method="GET" action="{{ route('admin.participants.index') }}" class="card mt-6 p-5">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="block">
                <span class="field-label">NIK</span>
                <input name="nik" value="{{ $filters['nik'] ?? '' }}" maxlength="16" inputmode="numeric" placeholder="Masukkan NIK" class="field-input font-mono">
            </label>
            <label class="block">
                <span class="field-label">Nama</span>
                <input name="nama" value="{{ $filters['nama'] ?? '' }}" placeholder="Nama peserta" class="field-input">
            </label>
            <label class="block">
                <span class="field-label">Tahun angkatan</span>
                <select name="tahun" class="field-select">
                    <option value="">Semua tahun</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" @selected(($filters['tahun'] ?? '') == $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="field-label">Kelas</span>
                <select name="batch_id" class="field-select">
                    <option value="">Semua kelas</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->id }}" @selected(($filters['batch_id'] ?? '') == $batch->id)>{{ $batch->nama }} · {{ $batch->tahun }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <x-icon name="search" class="size-4" />
                Cari peserta
            </button>
            <a href="{{ route('admin.participants.index') }}" class="btn btn-ghost btn-sm">Reset filter</a>
        </div>
    </form>

    <div class="table-shell mt-6">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>NIK</th>
                        <th>Nama peserta</th>
                        <th>Program / kelas</th>
                        <th>Tahun</th>
                        <th>Status pendaftaran</th>
                        <th>Nilai</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td><span class="font-mono text-xs text-ink/55">{{ $enrollment->participant->nik }}</span></td>
                            <td><span class="font-semibold text-ink">{{ $enrollment->participant->nama_lengkap }}</span></td>
                            <td>
                                <span class="block text-ink">{{ $enrollment->batch->program->nama }}</span>
                                <span class="mt-0.5 block text-xs text-ink/45">{{ $enrollment->batch->nama }}</span>
                            </td>
                            <td class="font-mono text-xs">{{ $enrollment->batch->tahun }}</td>
                            <td><span class="badge badge-neutral">{{ str_replace('_', ' ', $enrollment->status_pendaftaran->value) }}</span></td>
                            <td class="font-mono text-xs">{{ $enrollment->nilai_akhir === null ? '—' : number_format((float) $enrollment->nilai_akhir, 2, ',', '.') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.batches.show', $enrollment->batch) }}" class="btn btn-secondary btn-sm">Kelola</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">
                                <x-empty-state icon="search" title="Tidak ada peserta yang cocok dengan filter." description="Coba ubah kata kunci atau reset filter.">
                                    <a href="{{ route('admin.participants.index') }}" class="btn btn-secondary btn-sm">Reset filter</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($enrollments->hasPages())
            <div class="border-t border-ink/10 px-5 py-4">{{ $enrollments->links() }}</div>
        @endif
    </div>
@endsection
