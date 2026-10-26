@extends('layouts.app')

@section('title', 'Kelola '.$batch->nama)

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('admin.batches.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">&larr; Kembali ke angkatan</a>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight">{{ $batch->nama }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $batch->program->nama }} · {{ $batch->tahun }} · {{ $batch->kode_batch }}</p>
        </div>
        <a href="{{ route('admin.batches.edit', $batch) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit angkatan</a>
    </div>

    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Periksa kembali data yang dimasukkan.</p>
            <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="mt-7 rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-semibold">Daftarkan peserta</h2>
        <p class="mt-1 text-sm text-slate-600">Peserta dengan NIK yang sudah terdaftar akan dipakai kembali.</p>
        <form method="POST" action="{{ route('admin.batches.enrollments.store', $batch) }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @csrf
            <label class="block text-sm font-medium">NIK (16 digit)
                <input name="nik" value="{{ old('nik') }}" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
            </label>
            <label class="block text-sm font-medium">Nama lengkap
                <input name="nama_lengkap" value="{{ old('nama_lengkap') }}" required maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
            </label>
            <label class="block text-sm font-medium">Email (opsional)
                <input type="email" name="email" value="{{ old('email') }}" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
            </label>
            <label class="block text-sm font-medium">Nomor telepon (opsional)
                <input name="no_telepon" value="{{ old('no_telepon') }}" maxlength="30" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
            </label>
            <label class="block text-sm font-medium">Tanggal daftar
                <input type="date" name="tanggal_daftar" value="{{ old('tanggal_daftar', now()->toDateString()) }}" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
            </label>
            <label class="block text-sm font-medium">Status pendaftaran
                <select name="status_pendaftaran" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($enrollmentStatuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status_pendaftaran', \App\Enums\EnrollmentStatus::Terdaftar->value) === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
                    @endforeach
                </select>
            </label>
            <div class="sm:col-span-2 lg:col-span-3">
                <button class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Simpan pendaftaran</button>
                @if ($batch->kuota)
                    <span class="ml-2 text-sm text-slate-500">Terdaftar {{ $batch->enrollments->count() }} dari {{ $batch->kuota }} peserta</span>
                @endif
            </div>
        </form>
    </section>

    <section class="mt-7 rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">Daftar peserta &amp; nilai akhir</h2>
                <p class="mt-1 text-sm text-slate-600">Nilai kelulusan dihitung otomatis berdasarkan ambang {{ number_format((float) $batch->program->nilai_min_lulus, 2, ',', '.') }}.</p>
            </div>
            <span class="text-sm text-slate-500">{{ $batch->enrollments->count() }} peserta</span>
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Tanggal daftar</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Nilai akhir</th><th class="px-4 py-3">Kelulusan</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($batch->enrollments as $enrollment)
                        <tr>
                            <td class="px-4 py-3.5"><p class="font-medium">{{ $enrollment->participant->nama_lengkap }}</p><p class="mt-0.5 font-mono text-xs text-slate-500">{{ $enrollment->participant->nik }}</p></td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $enrollment->tanggal_daftar->format('d M Y') }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ ucfirst(str_replace('_', ' ', $enrollment->status_pendaftaran->value)) }}</td>
                            <td class="px-4 py-3.5">
                                <form method="POST" action="{{ route('admin.batches.scores.update', [$batch, $enrollment]) }}" class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="number" name="nilai_akhir" min="0" max="100" step="0.01" value="{{ $enrollment->nilai_akhir }}" aria-label="Nilai akhir {{ $enrollment->participant->nama_lengkap }}" class="w-24 rounded-lg border-slate-300 text-sm">
                                    <button class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-semibold hover:bg-slate-50">Simpan</button>
                                </form>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">{{ ucfirst(str_replace('_', ' ', $enrollment->status_kelulusan->value)) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada peserta di angkatan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-7 rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-semibold">Pertemuan &amp; absensi</h2>
        <form method="POST" action="{{ route('admin.batches.sessions.store', $batch) }}" class="mt-4 grid gap-4 sm:grid-cols-3">
            @csrf
            <label class="block text-sm font-medium">Pertemuan ke-
                <input type="number" name="pertemuan_ke" min="1" required value="{{ old('pertemuan_ke', $batch->trainingSessions->count() + 1) }}" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
            </label>
            <label class="block text-sm font-medium">Tanggal
                <input type="date" name="tanggal" required value="{{ old('tanggal', now()->toDateString()) }}" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
            </label>
            <label class="block text-sm font-medium">Topik (opsional)
                <input name="topik" value="{{ old('topik') }}" maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
            </label>
            <div class="sm:col-span-3"><button class="rounded-xl border border-emerald-300 px-4 py-2.5 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">Tambah pertemuan</button></div>
        </form>

        @forelse ($batch->trainingSessions as $session)
            <form method="POST" action="{{ route('admin.batches.attendance.update', [$batch, $session]) }}" class="mt-6 border-t border-slate-200 pt-5">
                @csrf @method('PUT')
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><h3 class="font-semibold">Pertemuan {{ $session->pertemuan_ke }}{{ $session->topik ? ' · '.$session->topik : '' }}</h3><p class="text-sm text-slate-500">{{ $session->tanggal->format('d M Y') }}</p></div>
                    <button class="rounded-lg bg-emerald-700 px-3.5 py-2 text-xs font-semibold text-white hover:bg-emerald-800">Simpan absensi</button>
                </div>
                @if ($batch->enrollments->isNotEmpty())
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($batch->enrollments as $enrollment)
                                    @php($attendance = $enrollment->attendance->firstWhere('training_session_id', $session->id))
                                    <tr>
                                        <td class="py-2.5 pr-4 font-medium">{{ $enrollment->participant->nama_lengkap }} <span class="ml-1 font-mono text-xs font-normal text-slate-500">{{ $enrollment->participant->nik }}</span></td>
                                        <td class="py-2.5 text-right">
                                            <select name="attendance[{{ $enrollment->id }}]" aria-label="Absensi {{ $enrollment->participant->nama_lengkap }}" class="rounded-lg border-slate-300 text-sm">
                                                <option value="">Belum dicatat</option>
                                                @foreach ($attendanceStatuses as $status)
                                                    <option value="{{ $status->value }}" @selected($attendance?->status === $status)>{{ ucfirst($status->value) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-500">Daftarkan peserta sebelum mencatat absensi.</p>
                @endif
            </form>
        @empty
            <p class="mt-5 border-t border-slate-200 pt-5 text-sm text-slate-500">Belum ada pertemuan. Tambahkan pertemuan untuk mulai mencatat absensi.</p>
        @endforelse
    </section>
@endsection
