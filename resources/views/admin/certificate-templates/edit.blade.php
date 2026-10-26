@extends('layouts.app')

@section('title', 'Template Sertifikat')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Template Sertifikat</h1>
            <p class="mt-2 text-sm text-slate-600">Atur gambar tanda tangan dan identitas penandatangan untuk PDF sertifikat.</p>
        </div>
        <a href="{{ route('admin.certificates.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
            Kembali ke sertifikat
        </a>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.certificate-templates.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 lg:grid-cols-[1fr_18rem]">
        @csrf
        @method('PUT')

        <div class="space-y-5">
            <label class="block text-sm font-medium">Nama penandatangan
                <input name="nama_penandatangan" value="{{ old('nama_penandatangan', $template->nama_penandatangan) }}" maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
            </label>

            <label class="block text-sm font-medium">Jabatan penandatangan
                <input name="jabatan_penandatangan" value="{{ old('jabatan_penandatangan', $template->jabatan_penandatangan) }}" maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
            </label>

            <label class="block text-sm font-medium">Gambar tanda tangan
                <input type="file" name="tanda_tangan" accept="image/png,image/jpeg" class="mt-1.5 block w-full rounded-lg border border-slate-300 p-2 text-sm">
                <span class="mt-1 block text-xs text-slate-500">PNG atau JPG, maksimal 1 MB. Gambar akan dicetak pada PDF sertifikat.</span>
            </label>

            @if ($template->tanda_tangan_path)
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="hapus_tanda_tangan" value="1" class="rounded border-slate-300">
                    Hapus gambar tanda tangan yang tersimpan
                </label>
            @endif

            <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                Simpan perubahan
            </button>
        </div>

        <aside class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pratinjau tanda tangan</h2>
            @if ($signatureDataUri)
                <img src="{{ $signatureDataUri }}" alt="Tanda tangan tersimpan" class="mt-3 max-h-40 w-full object-contain">
            @else
                <p class="mt-3 text-sm text-slate-500">Belum ada gambar tanda tangan.</p>
            @endif
            <p class="mt-4 text-xs text-slate-500">{{ $template->nama }} · versi {{ $template->versi }}</p>
        </aside>
    </form>
@endsection
