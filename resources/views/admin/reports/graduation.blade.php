@extends('layouts.backoffice')

@section('title', 'Rekap Kelulusan')

@section('content')
    @php
        $totalPeserta = $rows->sum('total_peserta');
        $totalLulus = $rows->sum('lulus');
        $totalTidakLulus = $rows->sum('tidak_lulus');
    @endphp

    <x-page-header eyebrow="Pelaporan" title="Rekap Kelulusan" subtitle="Ringkasan peserta per program dan tahun periode angkatan selesai." />

    <section class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach ([
            ['Total peserta', $totalPeserta],
            ['Lulus', $totalLulus],
            ['Tidak lulus', $totalTidakLulus],
        ] as [$label, $value])
            <article class="card p-5">
                <p class="stat-label">{{ $label }}</p>
                <p class="stat-value">{{ number_format($value, 0, ',', '.') }}</p>
            </article>
        @endforeach
    </section>

    <form method="GET" action="{{ route('admin.reports.graduation') }}" class="card mt-6 p-5">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label for="from" class="field-label">Tanggal selesai dari</label>
                <input id="from" name="from" type="date" value="{{ $filters['from'] }}" class="field-input">
            </div>
            <div>
                <label for="to" class="field-label">Tanggal selesai sampai</label>
                <input id="to" name="to" type="date" value="{{ $filters['to'] }}" class="field-input">
            </div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <div class="ml-auto flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.graduation.export', ['format' => 'xlsx'] + array_filter($filters)) }}" class="btn btn-secondary">
                    <x-icon name="download" class="size-4" />
                    Ekspor Excel
                </a>
                <a href="{{ route('admin.reports.graduation.export', ['format' => 'pdf'] + array_filter($filters)) }}" class="btn btn-secondary">
                    <x-icon name="download" class="size-4" />
                    Ekspor PDF
                </a>
            </div>
        </div>
        @foreach ($errors->all() as $error)
            <p class="field-error">{{ $error }}</p>
        @endforeach
    </form>

    <div class="table-shell mt-6">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Periode</th>
                        <th class="text-right">Total peserta</th>
                        <th class="text-right">Lulus</th>
                        <th class="text-right">Tidak lulus</th>
                        <th class="text-right">Belum dinilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td><span class="font-semibold text-ink">{{ $row->program }}</span></td>
                            <td class="font-mono text-xs">{{ $row->periode }}</td>
                            <td class="text-right font-mono text-xs">{{ $row->total_peserta }}</td>
                            <td class="text-right font-mono text-xs text-forest">{{ $row->lulus }}</td>
                            <td class="text-right font-mono text-xs text-clay">{{ $row->tidak_lulus }}</td>
                            <td class="text-right font-mono text-xs">{{ $row->belum_dinilai }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-empty">
                                <x-empty-state icon="chart" title="Tidak ada data kelulusan" description="Tidak ada peserta pada periode ini. Coba ubah filter tanggal." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
