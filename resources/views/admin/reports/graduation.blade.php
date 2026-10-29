@extends('layouts.app')

@section('title', 'Rekap Kelulusan')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Rekap Kelulusan</h1>
            <p class="mt-2 text-sm text-slate-600">Ringkasan peserta per program dan tahun periode angkatan selesai.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.graduation') }}" class="mt-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-5">
        <div>
            <label for="from" class="block text-sm font-medium text-slate-700">Tanggal selesai dari</label>
            <input id="from" name="from" type="date" value="{{ $filters['from'] }}" class="mt-1 rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label for="to" class="block text-sm font-medium text-slate-700">Tanggal selesai sampai</label>
            <input id="to" name="to" type="date" value="{{ $filters['to'] }}" class="mt-1 rounded-lg border-slate-300 text-sm">
        </div>
        <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Terapkan</button>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('admin.reports.graduation.export', ['format' => 'xlsx'] + array_filter($filters)) }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ekspor Excel</a>
            <a href="{{ route('admin.reports.graduation.export', ['format' => 'pdf'] + array_filter($filters)) }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ekspor PDF</a>
        </div>
        @foreach ($errors->all() as $error)
            <p class="w-full text-sm text-red-600">{{ $error }}</p>
        @endforeach
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Program</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3 text-right">Total peserta</th>
                        <th class="px-5 py-3 text-right">Lulus</th>
                        <th class="px-5 py-3 text-right">Tidak lulus</th>
                        <th class="px-5 py-3 text-right">Belum dinilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-5 py-3.5 font-medium">{{ $row->program }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $row->periode }}</td>
                            <td class="px-5 py-3.5 text-right">{{ $row->total_peserta }}</td>
                            <td class="px-5 py-3.5 text-right text-emerald-700">{{ $row->lulus }}</td>
                            <td class="px-5 py-3.5 text-right text-red-700">{{ $row->tidak_lulus }}</td>
                            <td class="px-5 py-3.5 text-right text-slate-600">{{ $row->belum_dinilai }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Tidak ada data kelulusan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
