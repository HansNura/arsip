@extends('layouts.app')

@section('title', 'Program Pelatihan')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Program Pelatihan</h1>
            <p class="mt-2 text-sm text-slate-600">Kelola katalog program dan nilai minimum kelulusan.</p>
        </div>
        <a href="{{ route('admin.programs.create') }}" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Tambah program</a>
    </div>

    @if ($errors->has('program'))
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('program') }}</div>
    @endif

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Kode</th><th class="px-5 py-3">Program</th><th class="px-5 py-3">Durasi</th><th class="px-5 py-3">Nilai lulus</th><th class="px-5 py-3">Angkatan</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($programs as $program)
                        <tr>
                            <td class="px-5 py-3.5 font-mono text-xs">{{ $program->kode_program }}</td>
                            <td class="px-5 py-3.5 font-medium">{{ $program->nama }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $program->durasi_jam ? $program->durasi_jam.' jam' : '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ number_format((float) $program->nilai_min_lulus, 2, ',', '.') }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $program->batches_count }}</td>
                            <td class="px-5 py-3.5">{{ $program->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.programs.edit', $program) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" onsubmit="return confirm('Arsipkan program ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">Arsipkan</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada program pelatihan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($programs->hasPages()) <div class="border-t border-slate-200 px-5 py-4">{{ $programs->links() }}</div> @endif
    </div>
@endsection
