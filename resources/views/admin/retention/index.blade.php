@extends('layouts.app')

@section('title', 'Kebijakan Retensi Data')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Kebijakan Retensi Data</h1>
            <p class="mt-2 text-sm text-slate-600">Atur pengarsipan alumni setelah masa retensi berdasarkan tanggal selesai pelatihan.</p>
        </div>
        <a href="{{ route('admin.retention.create') }}" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Tambah kebijakan</a>
    </div>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Retensi</th>
                        <th class="px-5 py-3">Tindakan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($policies as $policy)
                        <tr>
                            <td class="px-5 py-3.5 font-medium">{{ $policy->nama }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $policy->retention_years }} tahun setelah tanggal selesai</td>
                            <td class="px-5 py-3.5 text-slate-600">
                                @switch($policy->action)
                                    @case('cold_storage') Arsip statis (penyimpanan dingin) @break
                                    @case('arsipkan') Arsip dinamis (tetap tersedia di sistem) @break
                                    @case('hapus_lembut') Soft-delete (dapat dipulihkan) @break
                                @endswitch
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="{{ $policy->is_active ? 'text-emerald-700' : 'text-slate-500' }}">{{ $policy->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.retention.edit', $policy) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada kebijakan retensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-8">
        <div>
            <h2 class="text-xl font-semibold tracking-tight">Data alumni yang diarsipkan</h2>
            <p class="mt-1 text-sm text-slate-600">Data soft-delete dapat dipulihkan. Pemulihan arsip mengembalikan penyimpanan ke tier dinamis.</p>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">NIK</th>
                            <th class="px-5 py-3">Penyimpanan</th>
                            <th class="px-5 py-3">Diarsipkan</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($archivedParticipants as $participant)
                            <tr>
                                <td class="px-5 py-3.5 font-medium">{{ $participant->nama_lengkap }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-600">{{ $participant->nik }}</td>
                                <td class="px-5 py-3.5 text-slate-600">
                                    @if ($participant->trashed())
                                        Soft-delete
                                    @elseif ($participant->storage_tier === 'cold')
                                        Statis
                                    @else
                                        Dinamis
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">{{ ($participant->archived_at ?? $participant->deleted_at)?->format('d-m-Y H:i') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <form method="POST" action="{{ route('admin.retention.participants.restore', $participant->id) }}" onsubmit="return confirm('Pulihkan data alumni ini?')">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Pulihkan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada data alumni yang diarsipkan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($archivedParticipants->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">{{ $archivedParticipants->links() }}</div>
            @endif
        </div>
    </section>
@endsection
