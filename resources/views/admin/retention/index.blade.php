@extends('layouts.backoffice')

@section('title', 'Kebijakan Retensi Data')

@section('content')
    <x-page-header eyebrow="Administrasi" title="Kebijakan Retensi Data" subtitle="Atur pengarsipan alumni setelah masa retensi berdasarkan tanggal selesai pelatihan.">
        <a href="{{ route('admin.retention.create') }}" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="size-4" />
            Tambah kebijakan
        </a>
    </x-page-header>

    <section class="table-shell mt-6">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Retensi</th>
                        <th>Tindakan</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($policies as $policy)
                        <tr>
                            <td><span class="font-semibold text-ink">{{ $policy->nama }}</span></td>
                            <td><span class="font-mono text-xs">{{ $policy->retention_years }} tahun</span><span class="ml-2 text-xs text-ink/40">setelah selesai</span></td>
                            <td>
                                @switch($policy->action)
                                    @case('cold_storage') Arsip statis (penyimpanan dingin) @break
                                    @case('arsipkan') Arsip dinamis (tetap tersedia) @break
                                    @case('hapus_lembut') Soft-delete (dapat dipulihkan) @break
                                @endswitch
                            </td>
                            <td><span @class(['badge', 'badge-valid' => $policy->is_active, 'badge-neutral' => ! $policy->is_active])>{{ $policy->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('admin.retention.edit', $policy) }}" class="btn btn-secondary btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-empty">
                                <x-empty-state icon="archive" title="Belum ada kebijakan" description="Tambahkan kebijakan retensi untuk mengarsipkan alumni lama.">
                                    <a href="{{ route('admin.retention.create') }}" class="btn btn-primary btn-sm">Tambah kebijakan</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-8">
        <div class="page-header">
            <div>
                <h2 class="section-title">Data alumni yang diarsipkan</h2>
                <p class="section-subtitle">Data soft-delete dapat dipulihkan. Pemulihan mengembalikan penyimpanan ke tier dinamis.</p>
            </div>
        </div>

        <div class="table-shell mt-4">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NIK</th>
                            <th>Penyimpanan</th>
                            <th>Diarsipkan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($archivedParticipants as $participant)
                            <tr>
                                <td><span class="font-semibold text-ink">{{ $participant->nama_lengkap }}</span></td>
                                <td><span class="font-mono text-xs">{{ $participant->nik }}</span></td>
                                <td>
                                    @if ($participant->trashed())
                                        <span class="badge badge-cancelled">Soft-delete</span>
                                    @elseif ($participant->storage_tier === 'cold')
                                        <span class="badge badge-info">Statis</span>
                                    @else
                                        <span class="badge badge-neutral">Dinamis</span>
                                    @endif
                                </td>
                                <td class="font-mono text-xs">{{ ($participant->archived_at ?? $participant->deleted_at)?->format('d-m-Y H:i') ?? '—' }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.retention.participants.restore', $participant->id) }}" onsubmit="return confirm('Pulihkan data alumni ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm">Pulihkan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="table-empty">
                                    <x-empty-state icon="archive" title="Belum ada alumni terarsip" description="Data yang melewati masa retensi akan muncul di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($archivedParticipants->hasPages())
                <div class="border-t border-ink/10 px-5 py-4">{{ $archivedParticipants->links() }}</div>
            @endif
        </div>
    </section>
@endsection
