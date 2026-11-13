@extends('layouts.backoffice')

@section('title', 'Template Sertifikat')

@section('content')
    <x-page-header eyebrow="Sertifikasi" title="Template Sertifikat" subtitle="Atur gambar tanda tangan dan identitas penandatangan untuk PDF sertifikat.">
        <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary btn-sm">Kembali ke sertifikat</a>
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger mt-6">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.certificate-templates.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-6 lg:grid-cols-[1fr_20rem]">
        @csrf
        @method('PUT')

        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Identitas penandatangan</h2>
                    <p class="section-subtitle">Nama dan jabatan akan dicetak di bagian bawah sertifikat.</p>
                </div>
            </div>
            <div class="card-body space-y-5">
                <label class="block">
                    <span class="field-label">Nama penandatangan</span>
                    <input name="nama_penandatangan" value="{{ old('nama_penandatangan', $template->nama_penandatangan) }}" maxlength="255" class="field-input">
                </label>
                <label class="block">
                    <span class="field-label">Jabatan penandatangan</span>
                    <input name="jabatan_penandatangan" value="{{ old('jabatan_penandatangan', $template->jabatan_penandatangan) }}" maxlength="255" class="field-input">
                </label>
                <label class="block">
                    <span class="field-label">Gambar tanda tangan</span>
                    <input type="file" name="tanda_tangan" accept="image/png,image/jpeg" class="field-input p-2">
                    <span class="field-hint">PNG atau JPG, maksimal 1 MB. Gambar akan dicetak pada PDF sertifikat.</span>
                </label>

                @if ($template->tanda_tangan_path)
                    <label class="flex items-center gap-2.5 text-sm text-ink/65">
                        <input type="checkbox" name="hapus_tanda_tangan" value="1" class="size-4 rounded border-ink/20 text-forest focus:ring-forest/30">
                        Hapus gambar tanda tangan yang tersimpan
                    </label>
                @endif
            </div>
            <div class="flex justify-end border-t border-ink/10 px-5 py-4 sm:px-6">
                <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            </div>
        </section>

        <aside class="card h-fit">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Pratinjau</h2>
                    <p class="section-subtitle">{{ $template->nama }} · versi {{ $template->versi }}</p>
                </div>
            </div>
            <div class="card-body">
                @if ($signatureDataUri)
                    <div class="rounded-xl border border-ink/10 bg-white p-4">
                        <img src="{{ $signatureDataUri }}" alt="Tanda tangan tersimpan" class="mx-auto max-h-40 w-full object-contain">
                    </div>
                @else
                    <x-empty-state icon="certificate" title="Belum ada tanda tangan" description="Unggah PNG atau JPG untuk melihat pratinjau." />
                @endif
            </div>
        </aside>
    </form>
@endsection
