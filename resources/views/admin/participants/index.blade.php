@extends('layouts.app')

@section('title', 'Pencarian Peserta')

@section('content')
    <div>
        <h1 class="text-3xl font-semibold tracking-tight">Pencarian Peserta</h1>
        <p class="mt-2 text-sm text-slate-600">Cari peserta berdasarkan NIK, nama, tahun angkatan, atau kelas.</p>
    </div>

    <form method="GET" action="{{ route('admin.participants.index') }}" class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4">
        <label class="block text-sm font-medium">NIK
            <input name="nik" value="{{ $filters['nik'] ?? '' }}" maxlength="16" inputmode="numeric" placeholder="Masukkan NIK" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
        </label>
        <label class="block text-sm font-medium">Nama
            <input name="nama" value="{{ $filters['nama'] ?? '' }}" placeholder="Nama peserta" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
        </label>
        <label class="block text-sm font-medium">Tahun angkatan
            <select name="tahun" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                <option value="">Semua tahun</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}" @selected(($filters['tahun'] ?? '') == $year)>{{ $year }}</option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm font-medium">Kelas
            <select name="batch_id" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                <option value="">Semua kelas</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}" @selected(($filters['batch_id'] ?? '') == $batch->id)>{{ $batch->nama }} · {{ $batch->tahun }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex items-center gap-3 sm:col-span-2 lg:col-span-4">
            <button class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Cari peserta</button>
            <a href="{{ route('admin.participants.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset filter</a>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">NIK</th><th class="px-5 py-3">Nama peserta</th><th class="px-5 py-3">Program / kelas</th><th class="px-5 py-3">Tahun</th><th class="px-5 py-3">Status pendaftaran</th><th class="px-5 py-3">Nilai</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td class="px-5 py-3.5 font-mono text-xs">{{ $enrollment->participant->nik }}</td>
                            <td class="px-5 py-3.5 font-medium">{{ $enrollment->participant->nama_lengkap }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $enrollment->batch->program->nama }}<p class="mt-0.5 text-xs">{{ $enrollment->batch->nama }}</p></td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $enrollment->batch->tahun }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ ucfirst(str_replace('_', ' ', $enrollment->status_pendaftaran->value)) }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $enrollment->nilai_akhir === null ? '—' : number_format((float) $enrollment->nilai_akhir, 2, ',', '.') }}</td>
                            <td class="px-5 py-3.5 text-right"><a href="{{ route('admin.batches.show', $enrollment->batch) }}" class="font-semibold text-emerald-800 hover:underline">Kelola</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Tidak ada peserta yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($enrollments->hasPages()) <div class="border-t border-slate-200 px-5 py-4">{{ $enrollments->links() }}</div> @endif
    </div>
@endsection
