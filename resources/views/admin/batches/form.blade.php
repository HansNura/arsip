@extends('layouts.backoffice')

@section('title', $batch->exists ? 'Edit Angkatan' : 'Tambah Angkatan')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.batches.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest hover:text-forest-deep">
            <x-icon name="arrow-right" class="size-4 rotate-180" />
            Kembali ke angkatan
        </a>
    </div>

    <x-page-header eyebrow="Operasional" :title="$batch->exists ? 'Edit angkatan' : 'Tambah angkatan'"
                  subtitle="Atur program, periode, kapasitas, dan status kelas." />

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

    <form method="POST" action="{{ $batch->exists ? route('admin.batches.update', $batch) : route('admin.batches.store') }}" class="mt-6 max-w-4xl space-y-6">
        @csrf
        @if ($batch->exists)
            @method('PUT')
        @endif

        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Program &amp; identitas</h2>
                    <p class="section-subtitle">Satu angkatan terikat pada satu program pelatihan.</p>
                </div>
            </div>
            <div class="card-body grid gap-5 sm:grid-cols-2">
                <label class="block sm:col-span-2">
                    <span class="field-label">Program</span>
                    <select name="program_id" required class="field-select">
                        <option value="">Pilih program</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}" @selected(old('program_id', $batch->program_id) == $program->id)>
                                {{ $program->nama }}{{ $program->is_active ? '' : ' (nonaktif)' }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="field-label">Kode angkatan</span>
                    <input name="kode_batch" value="{{ old('kode_batch', $batch->kode_batch) }}" required maxlength="255" class="field-input font-mono">
                </label>
                <label class="block">
                    <span class="field-label">Nama kelas</span>
                    <input name="nama" value="{{ old('nama', $batch->nama) }}" required maxlength="255" class="field-input">
                </label>
                <label class="block">
                    <span class="field-label">Tahun</span>
                    <input type="number" min="2000" max="2100" name="tahun" value="{{ old('tahun', $batch->tahun ?? date('Y')) }}" required class="field-input font-mono">
                </label>
                <label class="block">
                    <span class="field-label">Kuota</span>
                    <input type="number" min="1" name="kuota" value="{{ old('kuota', $batch->kuota) }}" class="field-input">
                </label>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Jadwal &amp; lokasi</h2>
                    <p class="section-subtitle">Tanggal selesai dipakai sebagai basis retensi alumni.</p>
                </div>
            </div>
            <div class="card-body grid gap-5 sm:grid-cols-2">
                <label class="block">
                    <span class="field-label">Tanggal mulai</span>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $batch->tanggal_mulai?->format('Y-m-d')) }}" class="field-input">
                </label>
                <label class="block">
                    <span class="field-label">Tanggal selesai</span>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $batch->tanggal_selesai?->format('Y-m-d')) }}" class="field-input">
                </label>
                <label class="block">
                    <span class="field-label">Lokasi</span>
                    <input name="lokasi" value="{{ old('lokasi', $batch->lokasi) }}" maxlength="255" class="field-input">
                </label>
                <label class="block">
                    <span class="field-label">Status</span>
                    <select name="status" required class="field-select">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $batch->status?->value ?? \App\Enums\BatchStatus::Direncanakan->value) === $status->value)>
                                {{ ucfirst(str_replace('_', ' ', $status->value)) }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-ink/10 px-5 py-4 sm:px-6">
                <a href="{{ route('admin.batches.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan angkatan</button>
            </div>
        </section>
    </form>
@endsection
