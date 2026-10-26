@extends('layouts.app')

@section('title', 'Sertifikat')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Sertifikat</h1>
            <p class="mt-2 text-sm text-slate-600">Terbitkan, pantau, dan unduh sertifikat peserta yang lulus.</p>
        </div>
        <a href="{{ route('admin.certificate-templates.edit') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
            Template &amp; tanda tangan
        </a>
    </div>

    @if ($errors->has('certificate') || $errors->has('enrollment') || $errors->has('template'))
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('certificate') ?: $errors->first('enrollment') ?: $errors->first('template') }}
        </div>
    @endif

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Terbitkan per angkatan</h2>
        <form method="POST" action="{{ route('admin.certificates.store-batch-request') }}" class="mt-4 flex flex-wrap items-end gap-3">
            @csrf
            <label class="block text-sm font-medium">Angkatan
                <select name="batch_id" required class="mt-1.5 block min-w-72 rounded-lg border-slate-300 text-sm">
                    <option value="">Pilih angkatan</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->id }}" @selected(($filters['batch_id'] ?? '') == $batch->id)>
                            {{ $batch->program->nama }} · {{ $batch->nama }} · {{ $batch->tahun }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                Terbitkan semua yang lulus
            </button>
        </form>
        <p class="mt-2 text-xs text-slate-500">Hanya peserta berstatus lulus dan belum memiliki sertifikat aktif yang diproses.</p>
    </section>

    <form method="GET" action="{{ route('admin.certificates.index') }}" class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4">
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
        <label class="block text-sm font-medium">Angkatan
            <select name="batch_id" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                <option value="">Semua angkatan</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}" @selected(($filters['batch_id'] ?? '') == $batch->id)>
                        {{ $batch->nama }} · {{ $batch->tahun }}
                    </option>
                @endforeach
            </select>
        </label>
        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Terapkan</button>
            <a href="{{ route('admin.certificates.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    @if ($selectedBatch)
        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Peserta lulus belum bersertifikat — {{ $selectedBatch->nama }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $eligibleEnrollments->count() }} peserta menunggu penerbitan.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Peserta</th>
                            <th class="px-5 py-3">Nomor peserta</th>
                            <th class="px-5 py-3">Nilai akhir</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($eligibleEnrollments as $enrollment)
                            <tr>
                                <td class="px-5 py-3.5 font-medium text-slate-900">{{ $enrollment->participant->nama_lengkap }}</td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $enrollment->nomor_peserta ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $enrollment->nilai_akhir ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <form method="POST" action="{{ route('admin.certificates.store', $enrollment) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                            Terbitkan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-slate-500">Tidak ada peserta lulus yang menunggu penerbitan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Nomor</th>
                        <th class="px-5 py-3">Peserta</th>
                        <th class="px-5 py-3">Program</th>
                        <th class="px-5 py-3">Angkatan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Terbit</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($certificates as $certificate)
                        <tr>
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $certificate->nomor_sertifikat }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $certificate->enrollment?->participant?->nama_lengkap ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $certificate->enrollment?->batch?->program?->nama ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $certificate->enrollment?->batch?->nama ?? '—' }}
                                @if ($certificate->enrollment?->batch?->tahun)
                                    <span class="text-slate-400">· {{ $certificate->enrollment->batch->tahun }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium
                                    {{ $certificate->status === \App\Enums\CertificateStatus::Aktif
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($certificate->status->value) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $certificate->tanggal_terbit?->format('d-m-Y') ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                @if ($certificate->file_path)
                                    <a href="{{ route('admin.certificates.download', $certificate) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                                        Unduh PDF
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">Belum ada berkas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada sertifikat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($certificates->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>
@endsection
