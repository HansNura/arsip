@extends('layouts.backoffice')

@section('title', 'Penerbitan Ulang')

@section('content')
    <x-page-header eyebrow="Jejak audit" title="Penerbitan Ulang" subtitle="Kelola permohonan reissue sertifikat dan penerbitan penggantinya.">
        <a href="{{ route('admin.reissues.create') }}" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="size-4" />
            Ajukan reissue
        </a>
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger mt-6">{{ $errors->first() }}</div>
    @endif

    <form method="GET" action="{{ route('admin.reissues.index') }}" class="card mt-6 p-5">
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
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="{{ route('admin.reissues.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            </div>
        </div>
    </form>

    <div class="table-shell mt-6">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sertifikat asal</th>
                        <th>Pemohon</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reissues as $reissue)
                        @php
                            $badge = match ($reissue->status) {
                                \App\Enums\ReissueStatus::Diajukan => 'badge-pending',
                                \App\Enums\ReissueStatus::Disetujui => 'badge-info',
                                \App\Enums\ReissueStatus::Ditolak => 'badge-cancelled',
                                \App\Enums\ReissueStatus::Selesai => 'badge-valid',
                            };
                        @endphp
                        <tr>
                            <td>
                                <span class="font-mono text-xs font-medium text-ink">{{ $reissue->originalCertificate?->nomor_sertifikat ?? '—' }}</span>
                                <p class="mt-0.5 text-xs text-ink/45">{{ $reissue->originalCertificate?->enrollment?->participant?->nama_lengkap ?? '—' }}</p>
                            </td>
                            <td>{{ $reissue->pemohon_nama ?? '—' }}</td>
                            <td class="max-w-xs">{{ \Illuminate\Support\Str::limit($reissue->alasan, 60) }}</td>
                            <td><span class="badge {{ $badge }}">{{ ucfirst($reissue->status->value) }}</span></td>
                            <td class="font-mono text-xs">{{ $reissue->created_at?->format('d-m-Y H:i') ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.reissues.show', $reissue) }}" class="btn btn-secondary btn-sm">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-empty">
                                <x-empty-state icon="reissue" title="Belum ada permohonan" description="Ajukan penerbitan ulang untuk sertifikat yang masih aktif.">
                                    <a href="{{ route('admin.reissues.create') }}" class="btn btn-primary btn-sm">Ajukan reissue</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reissues->hasPages())
            <div class="border-t border-ink/10 px-5 py-4">{{ $reissues->links() }}</div>
        @endif
    </div>
@endsection
