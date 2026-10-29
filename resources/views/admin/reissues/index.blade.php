@extends('layouts.app')

@section('title', 'Penerbitan Ulang')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Penerbitan Ulang</h1>
            <p class="mt-2 text-sm text-slate-600">Kelola permohonan reissue sertifikat dan penerbitan penggantinya.</p>
        </div>
        <a href="{{ route('admin.reissues.create') }}" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
            Ajukan reissue
        </a>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="GET" action="{{ route('admin.reissues.index') }}" class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4">
        <label class="block text-sm font-medium">Cari nomor atau nama
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="PEL/2026/0001 atau nama peserta" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
        </label>
        <label class="block text-sm font-medium">Status
            <select name="status" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst($status->value) }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Terapkan</button>
            <a href="{{ route('admin.reissues.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Sertifikat asal</th>
                        <th class="px-5 py-3">Pemohon</th>
                        <th class="px-5 py-3">Alasan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Diajukan</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reissues as $reissue)
                        @php
                            $badge = match ($reissue->status) {
                                \App\Enums\ReissueStatus::Diajukan => 'bg-amber-100 text-amber-800',
                                \App\Enums\ReissueStatus::Disetujui => 'bg-sky-100 text-sky-800',
                                \App\Enums\ReissueStatus::Ditolak => 'bg-red-100 text-red-800',
                                \App\Enums\ReissueStatus::Selesai => 'bg-emerald-100 text-emerald-800',
                            };
                        @endphp
                        <tr>
                            <td class="px-5 py-3.5 font-medium text-slate-900">
                                {{ $reissue->originalCertificate?->nomor_sertifikat ?? '—' }}
                                <p class="mt-0.5 text-xs font-normal text-slate-500">
                                    {{ $reissue->originalCertificate?->enrollment?->participant?->nama_lengkap ?? '—' }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $reissue->pemohon_nama ?? '—' }}</td>
                            <td class="max-w-xs px-5 py-3.5 text-slate-600">{{ \Illuminate\Support\Str::limit($reissue->alasan, 60) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">{{ ucfirst($reissue->status->value) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $reissue->created_at?->format('d-m-Y H:i') ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.reissues.show', $reissue) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada permohonan reissue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reissues->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $reissues->links() }}
            </div>
        @endif
    </div>
@endsection
