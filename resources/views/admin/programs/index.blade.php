@extends('layouts.backoffice')

@section('title', 'Program Pelatihan')

@section('content')
    <x-page-header eyebrow="Katalog" title="Program Pelatihan" subtitle="Kelola katalog program dan nilai minimum kelulusan.">
        <a href="{{ route('admin.programs.create') }}" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="size-4" />
            Tambah program
        </a>
    </x-page-header>

    @if ($errors->has('program'))
        <div class="alert alert-danger mt-6">{{ $errors->first('program') }}</div>
    @endif

    <div class="table-shell mt-6">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Program</th>
                        <th>Durasi</th>
                        <th>Nilai lulus</th>
                        <th>Angkatan</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($programs as $program)
                        <tr>
                            <td><span class="font-mono text-xs text-ink/55">{{ $program->kode_program }}</span></td>
                            <td><span class="font-semibold text-ink">{{ $program->nama }}</span></td>
                            <td>{{ $program->durasi_jam ? $program->durasi_jam.' jam' : '—' }}</td>
                            <td class="font-mono text-xs">{{ number_format((float) $program->nilai_min_lulus, 2, ',', '.') }}</td>
                            <td>{{ $program->batches_count }}</td>
                            <td>
                                <span @class(['badge', 'badge-valid' => $program->is_active, 'badge-neutral' => ! $program->is_active])>
                                    {{ $program->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" onsubmit="return confirm('Arsipkan program ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Arsipkan</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">
                                <x-empty-state icon="book" title="Belum ada program" description="Tambahkan program pertama untuk mulai membuat angkatan.">
                                    <a href="{{ route('admin.programs.create') }}" class="btn btn-primary btn-sm">Tambah program</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($programs->hasPages())
            <div class="border-t border-ink/10 px-5 py-4">{{ $programs->links() }}</div>
        @endif
    </div>
@endsection
