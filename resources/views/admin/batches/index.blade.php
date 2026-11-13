@extends('layouts.backoffice')

@section('title', 'Angkatan')

@section('content')
    <x-page-header eyebrow="Operasional" title="Angkatan & Kelas" subtitle="Kelola kelas pelatihan dan buka daftar pendaftaran, absensi, serta nilai.">
        <a href="{{ route('admin.batches.create') }}" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="size-4" />
            Tambah angkatan
        </a>
    </x-page-header>

    @if ($errors->has('batch'))
        <div class="alert alert-danger mt-6">{{ $errors->first('batch') }}</div>
    @endif

    <div class="table-shell mt-6">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Angkatan</th>
                        <th>Program</th>
                        <th>Tahun</th>
                        <th>Periode</th>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        @php
                            $statusClass = match ($batch->status->value) {
                                'berjalan' => 'badge-info',
                                'selesai' => 'badge-valid',
                                'dibatalkan' => 'badge-cancelled',
                                default => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.batches.show', $batch) }}" class="font-semibold text-ink hover:text-forest">{{ $batch->nama }}</a>
                                <p class="mt-0.5 font-mono text-xs text-ink/45">{{ $batch->kode_batch }}</p>
                            </td>
                            <td>{{ $batch->program->nama }}</td>
                            <td class="font-mono text-xs">{{ $batch->tahun }}</td>
                            <td>{{ $batch->tanggal_mulai?->format('d M Y') ?? '—' }} – {{ $batch->tanggal_selesai?->format('d M Y') ?? '—' }}</td>
                            <td class="font-mono text-xs">{{ $batch->enrollments_count }}{{ $batch->kuota ? ' / '.$batch->kuota : '' }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ str_replace('_', ' ', $batch->status->value) }}</span></td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.batches.show', $batch) }}" class="btn btn-secondary btn-sm">Kelola</a>
                                    <a href="{{ route('admin.batches.edit', $batch) }}" class="btn btn-ghost btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.batches.destroy', $batch) }}" onsubmit="return confirm('Arsipkan angkatan ini?')">
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
                                <x-empty-state icon="layers" title="Belum ada angkatan" description="Buat angkatan pertama untuk mulai mendaftarkan peserta.">
                                    <a href="{{ route('admin.batches.create') }}" class="btn btn-primary btn-sm">Tambah angkatan</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($batches->hasPages())
            <div class="border-t border-ink/10 px-5 py-4">{{ $batches->links() }}</div>
        @endif
    </div>
@endsection
