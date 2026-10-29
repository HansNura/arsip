@extends('layouts.app')

@section('title', 'Ajukan Penerbitan Ulang')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Ajukan Penerbitan Ulang</h1>
            <p class="mt-2 text-sm text-slate-600">Permohonan hanya dapat diajukan untuk sertifikat berstatus aktif.</p>
        </div>
        <a href="{{ route('admin.reissues.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
            Kembali
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

    <form method="POST" action="{{ route('admin.reissues.store') }}" class="mt-6 grid gap-5 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-2 sm:p-6">
        @csrf

        <label class="block text-sm font-medium sm:col-span-2">Nomor sertifikat asal
            <input name="nomor_sertifikat" value="{{ old('nomor_sertifikat') }}" required placeholder="Contoh: PEL/2026/0001" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
        </label>

        <label class="block text-sm font-medium">Nama pemohon
            <input name="pemohon_nama" value="{{ old('pemohon_nama') }}" maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
        </label>

        <label class="block text-sm font-medium">Kontak pemohon
            <input name="pemohon_kontak" value="{{ old('pemohon_kontak') }}" maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">
        </label>

        <label class="block text-sm font-medium sm:col-span-2">Alasan penerbitan ulang
            <textarea name="alasan" rows="4" required maxlength="2000" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm">{{ old('alasan') }}</textarea>
            <span class="mt-1 block text-xs text-slate-500">Contoh: sertifikat hilang, rusak, atau data tidak sesuai.</span>
        </label>

        <div class="sm:col-span-2">
            <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                Simpan permohonan
            </button>
        </div>
    </form>
@endsection
