@extends('layouts.backoffice')

@section('title', 'Kelola '.$batch->nama)

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.batches.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest hover:text-forest-deep">
            <x-icon name="arrow-right" class="size-4 rotate-180" />
            Kembali ke angkatan
        </a>
    </div>

    <x-page-header eyebrow="Operasional" :title="$batch->nama"
                  :subtitle="$batch->program->nama.' · '.$batch->tahun.' · '.$batch->kode_batch">
        <a href="{{ route('admin.batches.edit', $batch) }}" class="btn btn-secondary btn-sm">Edit angkatan</a>
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger mt-6">
            <p class="font-semibold">Periksa kembali data yang dimasukkan.</p>
            <ul class="mt-1 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach ([
            ['layers', 'Peserta', $batch->enrollments->count(), $batch->kuota ? 'dari '.$batch->kuota.' kuota' : 'tanpa kuota'],
            ['book', 'Pertemuan', $batch->trainingSessions->count(), 'sesi tercatat'],
            ['certificate', 'Nilai minimum', number_format((float) $batch->program->nilai_min_lulus, 2, ',', '.'), 'ambang kelulusan'],
        ] as [$icon, $label, $value, $note])
            <article class="card flex items-center gap-4 p-5">
                <span class="grid size-10 place-items-center rounded-xl border border-forest/15 bg-forest/5 text-forest">
                    <x-icon :name="$icon" class="size-5" />
                </span>
                <div>
                    <p class="stat-label">{{ $label }}</p>
                    <p class="mt-1 font-display text-2xl font-semibold tracking-[-0.02em] text-ink">{{ $value }}</p>
                    <p class="text-xs text-ink/45">{{ $note }}</p>
                </div>
            </article>
        @endforeach
    </section>

    <section class="card mt-6">
        <div class="card-header">
            <div>
                <h2 class="section-title">Daftarkan peserta</h2>
                <p class="section-subtitle">Peserta dengan NIK yang sudah terdaftar akan dipakai kembali.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.batches.enrollments.store', $batch) }}" class="card-body grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @csrf
            <label class="block">
                <span class="field-label">NIK (16 digit)</span>
                <input name="nik" value="{{ old('nik') }}" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required class="field-input font-mono">
            </label>
            <label class="block">
                <span class="field-label">Nama lengkap</span>
                <input name="nama_lengkap" value="{{ old('nama_lengkap') }}" required maxlength="255" class="field-input">
            </label>
            <label class="block">
                <span class="field-label">Email (opsional)</span>
                <input type="email" name="email" value="{{ old('email') }}" class="field-input">
            </label>
            <label class="block">
                <span class="field-label">Nomor telepon (opsional)</span>
                <input name="no_telepon" value="{{ old('no_telepon') }}" maxlength="30" class="field-input">
            </label>
            <label class="block">
                <span class="field-label">Tanggal daftar</span>
                <input type="date" name="tanggal_daftar" value="{{ old('tanggal_daftar', now()->toDateString()) }}" required class="field-input">
            </label>
            <label class="block">
                <span class="field-label">Status pendaftaran</span>
                <select name="status_pendaftaran" required class="field-select">
                    @foreach ($enrollmentStatuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status_pendaftaran', \App\Enums\EnrollmentStatus::Terdaftar->value) === $status->value)>
                            {{ ucfirst(str_replace('_', ' ', $status->value)) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-center gap-3 sm:col-span-2 lg:col-span-3">
                <button type="submit" class="btn btn-primary">Simpan pendaftaran</button>
                @if ($batch->kuota)
                    <span class="text-xs text-ink/45">Terdaftar {{ $batch->enrollments->count() }} dari {{ $batch->kuota }} peserta</span>
                @endif
            </div>
        </form>
    </section>

    <section class="card mt-6">
        <div class="card-header">
            <div>
                <h2 class="section-title">Daftar peserta &amp; nilai akhir</h2>
                <p class="section-subtitle">Nilai kelulusan dihitung otomatis berdasarkan ambang {{ number_format((float) $batch->program->nilai_min_lulus, 2, ',', '.') }}.</p>
            </div>
            <span class="badge badge-neutral">{{ $batch->enrollments->count() }} peserta</span>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Tanggal daftar</th>
                        <th>Status</th>
                        <th>Nilai akhir</th>
                        <th>Kelulusan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batch->enrollments as $enrollment)
                        @php
                            $graduationClass = match ($enrollment->status_kelulusan->value) {
                                'lulus' => 'badge-valid',
                                'tidak_lulus' => 'badge-cancelled',
                                default => 'badge-pending',
                            };
                        @endphp
                        <tr>
                            <td>
                                <p class="font-semibold text-ink">{{ $enrollment->participant->nama_lengkap }}</p>
                                <p class="mt-0.5 font-mono text-xs text-ink/45">{{ $enrollment->participant->nik }}</p>
                            </td>
                            <td>{{ $enrollment->tanggal_daftar->format('d M Y') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $enrollment->status_pendaftaran->value)) }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.batches.scores.update', [$batch, $enrollment]) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" name="nilai_akhir" min="0" max="100" step="0.01" value="{{ $enrollment->nilai_akhir }}" aria-label="Nilai akhir {{ $enrollment->participant->nama_lengkap }}" class="field-input mt-0 w-24 py-1.5 text-xs">
                                    <button type="submit" class="btn btn-secondary btn-sm">Simpan</button>
                                </form>
                            </td>
                            <td><span class="badge {{ $graduationClass }}">{{ str_replace('_', ' ', $enrollment->status_kelulusan->value) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-empty">
                                <x-empty-state icon="users" title="Belum ada peserta" description="Daftarkan peserta melalui formulir di atas." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card mt-6">
        <div class="card-header">
            <div>
                <h2 class="section-title">Pertemuan &amp; absensi</h2>
                <p class="section-subtitle">Catat kehadiran per pertemuan untuk setiap peserta.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.batches.sessions.store', $batch) }}" class="card-body grid gap-5 border-b border-ink/10 sm:grid-cols-3">
            @csrf
            <label class="block">
                <span class="field-label">Pertemuan ke-</span>
                <input type="number" name="pertemuan_ke" min="1" required value="{{ old('pertemuan_ke', $batch->trainingSessions->count() + 1) }}" class="field-input font-mono">
            </label>
            <label class="block">
                <span class="field-label">Tanggal</span>
                <input type="date" name="tanggal" required value="{{ old('tanggal', now()->toDateString()) }}" class="field-input">
            </label>
            <label class="block">
                <span class="field-label">Topik (opsional)</span>
                <input name="topik" value="{{ old('topik') }}" maxlength="255" class="field-input">
            </label>
            <div class="sm:col-span-3">
                <button type="submit" class="btn btn-secondary">
                    <x-icon name="plus" class="size-4" />
                    Tambah pertemuan
                </button>
            </div>
        </form>

        @forelse ($batch->trainingSessions as $session)
            <form method="POST" action="{{ route('admin.batches.attendance.update', [$batch, $session]) }}" class="border-t border-ink/10 px-5 py-6 sm:px-6">
                @csrf
                @method('PUT')
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-lg font-semibold">Pertemuan {{ $session->pertemuan_ke }}{{ $session->topik ? ' · '.$session->topik : '' }}</h3>
                        <p class="mt-0.5 text-sm text-ink/45">{{ $session->tanggal->format('d M Y') }}</p>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan absensi</button>
                </div>

                @if ($batch->enrollments->isNotEmpty())
                    <div class="mt-4 overflow-x-auto">
                        <table class="data-table">
                            <tbody>
                                @foreach ($batch->enrollments as $enrollment)
                                    @php($attendance = $enrollment->attendance->firstWhere('training_session_id', $session->id))
                                    <tr>
                                        <td>
                                            <span class="font-medium text-ink">{{ $enrollment->participant->nama_lengkap }}</span>
                                            <span class="ml-2 font-mono text-xs text-ink/40">{{ $enrollment->participant->nik }}</span>
                                        </td>
                                        <td class="text-right">
                                            <select name="attendance[{{ $enrollment->id }}]" aria-label="Absensi {{ $enrollment->participant->nama_lengkap }}" class="field-select mt-0 ml-auto w-44 py-1.5 text-xs">
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
                    <p class="mt-4 text-sm text-ink/45">Daftarkan peserta sebelum mencatat absensi.</p>
                @endif
            </form>
        @empty
            <div class="border-t border-ink/10 px-5 py-6 sm:px-6">
                <x-empty-state icon="book" title="Belum ada pertemuan" description="Tambahkan pertemuan untuk mulai mencatat absensi." />
            </div>
        @endforelse
    </section>
@endsection
