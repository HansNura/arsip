@extends('layouts.app')

@section('title', $batch->exists ? 'Edit Angkatan' : 'Tambah Angkatan')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('admin.batches.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">&larr; Kembali ke angkatan</a>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight">{{ $batch->exists ? 'Edit angkatan' : 'Tambah angkatan' }}</h1>
        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><p class="font-semibold">Periksa kembali data yang dimasukkan.</p><ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ $batch->exists ? route('admin.batches.update', $batch) : route('admin.batches.store') }}" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @if ($batch->exists) @method('PUT') @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium sm:col-span-2">Program
                    <select name="program_id" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                        <option value="">Pilih program</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}" @selected(old('program_id', $batch->program_id) == $program->id)>{{ $program->nama }}{{ $program->is_active ? '' : ' (nonaktif)' }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm font-medium">Kode angkatan
                    <input name="kode_batch" value="{{ old('kode_batch', $batch->kode_batch) }}" required maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-medium">Nama kelas
                    <input name="nama" value="{{ old('nama', $batch->nama) }}" required maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-medium">Tahun
                    <input type="number" min="2000" max="2100" name="tahun" value="{{ old('tahun', $batch->tahun ?? date('Y')) }}" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-medium">Kuota
                    <input type="number" min="1" name="kuota" value="{{ old('kuota', $batch->kuota) }}" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-medium">Tanggal mulai
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $batch->tanggal_mulai?->format('Y-m-d')) }}" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-medium">Tanggal selesai
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $batch->tanggal_selesai?->format('Y-m-d')) }}" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-medium">Lokasi
                    <input name="lokasi" value="{{ old('lokasi', $batch->lokasi) }}" maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                </label>
                <label class="block text-sm font-medium">Status
                    <select name="status" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $batch->status?->value ?? \App\Enums\BatchStatus::Direncanakan->value) === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.batches.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Batal</a>
                <button class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Simpan</button>
            </div>
        </form>
    </div>
@endsection
