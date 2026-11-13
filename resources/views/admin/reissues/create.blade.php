@extends('layouts.backoffice')

@section('title', 'Ajukan Penerbitan Ulang')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.reissues.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest hover:text-forest-deep">
            <x-icon name="arrow-right" class="size-4 rotate-180" />
            Kembali ke penerbitan ulang
        </a>
    </div>

    <x-page-header eyebrow="Jejak audit" title="Ajukan Penerbitan Ulang" subtitle="Permohonan hanya dapat diajukan untuk sertifikat berstatus aktif." />

    @if ($errors->any())
        <div class="alert alert-danger mt-6">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.reissues.store') }}" class="card mt-6 max-w-3xl">
        <div class="card-header">
            <div>
                <h2 class="section-title">Data permohonan</h2>
                <p class="section-subtitle">Sertifikat asal harus aktif dan belum memiliki permohonan terbuka.</p>
            </div>
        </div>
        <div class="card-body grid gap-5 sm:grid-cols-2">
            @csrf
            <label class="block sm:col-span-2">
                <span class="field-label">Nomor sertifikat asal</span>
                <input name="nomor_sertifikat" value="{{ old('nomor_sertifikat') }}" required placeholder="Contoh: PEL/2026/0001" class="field-input font-mono">
            </label>
            <label class="block">
                <span class="field-label">Nama pemohon</span>
                <input name="pemohon_nama" value="{{ old('pemohon_nama') }}" maxlength="255" class="field-input">
            </label>
            <label class="block">
                <span class="field-label">Kontak pemohon</span>
                <input name="pemohon_kontak" value="{{ old('pemohon_kontak') }}" maxlength="255" class="field-input">
            </label>
            <label class="block sm:col-span-2">
                <span class="field-label">Alasan penerbitan ulang</span>
                <textarea name="alasan" rows="4" required maxlength="2000" class="field-textarea">{{ old('alasan') }}</textarea>
                <span class="field-hint">Contoh: sertifikat hilang, rusak, atau data tidak sesuai.</span>
            </label>
        </div>
        <div class="flex justify-end gap-3 border-t border-ink/10 px-5 py-4 sm:px-6">
            <a href="{{ route('admin.reissues.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan permohonan</button>
        </div>
    </form>
@endsection
