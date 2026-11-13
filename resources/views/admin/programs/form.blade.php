@extends('layouts.backoffice')

@section('title', $program->exists ? 'Edit Program' : 'Tambah Program')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.programs.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest hover:text-forest-deep">
            <x-icon name="arrow-right" class="size-4 rotate-180" />
            Kembali ke program
        </a>
    </div>

    <x-page-header eyebrow="Katalog" :title="$program->exists ? 'Edit program' : 'Tambah program'"
                  subtitle="Lengkapi identitas program dan aturan kelulusannya." />

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

    <form method="POST" action="{{ $program->exists ? route('admin.programs.update', $program) : route('admin.programs.store') }}" class="mt-6 max-w-4xl space-y-6">
        @csrf
        @if ($program->exists)
            @method('PUT')
        @endif

        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Identitas program</h2>
                    <p class="section-subtitle">Kode dan nama dipakai pada sertifikat dan laporan.</p>
                </div>
            </div>
            <div class="card-body grid gap-5 sm:grid-cols-2">
                <label class="block">
                    <span class="field-label">Kode program</span>
                    <input name="kode_program" value="{{ old('kode_program', $program->kode_program) }}" required maxlength="255" class="field-input font-mono">
                </label>
                <label class="block">
                    <span class="field-label">Nama program</span>
                    <input name="nama" value="{{ old('nama', $program->nama) }}" required maxlength="255" class="field-input">
                </label>
                <label class="block">
                    <span class="field-label">Durasi (jam)</span>
                    <input type="number" min="1" name="durasi_jam" value="{{ old('durasi_jam', $program->durasi_jam) }}" class="field-input">
                </label>
                <label class="block">
                    <span class="field-label">Nilai minimum lulus</span>
                    <input type="number" min="0" max="100" step="0.01" name="nilai_min_lulus" value="{{ old('nilai_min_lulus', $program->nilai_min_lulus ?? 70) }}" required class="field-input">
                </label>
                <label class="block sm:col-span-2">
                    <span class="field-label">Deskripsi</span>
                    <textarea name="deskripsi" rows="3" class="field-textarea">{{ old('deskripsi', $program->deskripsi) }}</textarea>
                </label>
                <label class="block sm:col-span-2">
                    <span class="field-label">Standar kompetensi</span>
                    <textarea name="standar_kompetensi" rows="3" class="field-textarea">{{ old('standar_kompetensi', $program->standar_kompetensi) }}</textarea>
                </label>
            </div>
        </section>

        <section class="card">
            <div class="card-body">
                <input type="hidden" name="is_active" value="0">
                <label class="flex items-start gap-3 text-sm text-ink/70">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $program->exists ? $program->is_active : true)) class="mt-0.5 size-4 rounded border-ink/20 text-forest focus:ring-forest/30">
                    <span>
                        <span class="block font-medium text-ink">Program aktif</span>
                        <span class="mt-0.5 block text-xs text-ink/45">Program aktif dapat dipilih untuk angkatan baru.</span>
                    </span>
                </label>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-ink/10 px-5 py-4 sm:px-6">
                <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan program</button>
            </div>
        </section>
    </form>
@endsection
