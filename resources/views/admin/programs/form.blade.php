@extends('layouts.app')

@section('title', $program->exists ? 'Edit Program' : 'Tambah Program')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('admin.programs.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">&larr; Kembali ke program</a>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight">{{ $program->exists ? 'Edit program' : 'Tambah program' }}</h1>

        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Periksa kembali data yang dimasukkan.</p>
                <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ $program->exists ? route('admin.programs.update', $program) : route('admin.programs.store') }}" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @if ($program->exists) @method('PUT') @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">Kode program
                    <input name="kode_program" value="{{ old('kode_program', $program->kode_program) }}" required maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-medium">Nama program
                    <input name="nama" value="{{ old('nama', $program->nama) }}" required maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-medium">Durasi (jam)
                    <input type="number" min="1" name="durasi_jam" value="{{ old('durasi_jam', $program->durasi_jam) }}" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-medium">Nilai minimum lulus
                    <input type="number" min="0" max="100" step="0.01" name="nilai_min_lulus" value="{{ old('nilai_min_lulus', $program->nilai_min_lulus ?? 70) }}" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
            </div>
            <label class="block text-sm font-medium">Deskripsi
                <textarea name="deskripsi" rows="3" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">{{ old('deskripsi', $program->deskripsi) }}</textarea>
            </label>
            <label class="block text-sm font-medium">Standar kompetensi
                <textarea name="standar_kompetensi" rows="3" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">{{ old('standar_kompetensi', $program->standar_kompetensi) }}</textarea>
            </label>
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm font-medium">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $program->exists ? $program->is_active : true)) class="rounded border-slate-300 text-emerald-700">
                Program aktif dan dapat dipilih untuk angkatan baru
            </label>
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.programs.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Batal</a>
                <button class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Simpan</button>
            </div>
        </form>
    </div>
@endsection
