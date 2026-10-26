@extends('layouts.app')

@section('title', 'Angkatan')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Angkatan &amp; Kelas</h1>
            <p class="mt-2 text-sm text-slate-600">Kelola kelas pelatihan dan buka daftar pendaftaran, absensi, serta nilai.</p>
        </div>
        <a href="{{ route('admin.batches.create') }}" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Tambah angkatan</a>
    </div>

    @if ($errors->has('batch'))
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('batch') }}</div>
    @endif

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Angkatan</th><th class="px-5 py-3">Program</th><th class="px-5 py-3">Tahun</th><th class="px-5 py-3">Periode</th><th class="px-5 py-3">Peserta</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($batches as $batch)
                        <tr>
                            <td class="px-5 py-3.5"><a href="{{ route('admin.batches.show', $batch) }}" class="font-medium text-emerald-800 hover:underline">{{ $batch->nama }}</a><p class="mt-0.5 font-mono text-xs text-slate-500">{{ $batch->kode_batch }}</p></td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $batch->program->nama }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $batch->tahun }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $batch->tanggal_mulai?->format('d M Y') ?? '—' }} – {{ $batch->tanggal_selesai?->format('d M Y') ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $batch->enrollments_count }}{{ $batch->kuota ? ' / '.$batch->kuota : '' }}</td>
                            <td class="px-5 py-3.5">{{ str_replace('_', ' ', $batch->status->value) }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.batches.show', $batch) }}" class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">Kelola</a>
                                    <a href="{{ route('admin.batches.edit', $batch) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('admin.batches.destroy', $batch) }}" onsubmit="return confirm('Arsipkan angkatan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">Arsipkan</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada angkatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($batches->hasPages()) <div class="border-t border-slate-200 px-5 py-4">{{ $batches->links() }}</div> @endif
    </div>
@endsection
