@extends('layouts.app')

@section('title', $policy->exists ? 'Edit Kebijakan Retensi' : 'Tambah Kebijakan Retensi')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-3xl font-semibold tracking-tight">{{ $policy->exists ? 'Edit Kebijakan Retensi' : 'Tambah Kebijakan Retensi' }}</h1>
        <p class="mt-2 text-sm text-slate-600">Kebijakan ini berlaku untuk peserta yang lulus dan seluruh pelatihannya selesai lebih dari masa retensi.</p>

        <form method="POST" action="{{ $policy->exists ? route('admin.retention.update', $policy) : route('admin.retention.store') }}" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @if ($policy->exists)
                @method('PUT')
            @endif

            <div>
                <label for="nama" class="block text-sm font-medium text-slate-700">Nama kebijakan</label>
                <input id="nama" name="nama" type="text" value="{{ old('nama', $policy->nama) }}" required maxlength="120" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                @error('nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="retention_years" class="block text-sm font-medium text-slate-700">Masa retensi (tahun)</label>
                <input id="retention_years" name="retention_years" type="number" min="1" max="100" value="{{ old('retention_years', $policy->retention_years) }}" required class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                @error('retention_years') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="action" class="block text-sm font-medium text-slate-700">Tindakan setelah masa retensi</label>
                <select id="action" name="action" required class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                    <option value="cold_storage" @selected(old('action', $policy->action) === 'cold_storage')>Arsip statis — penyimpanan dingin</option>
                    <option value="arsipkan" @selected(old('action', $policy->action) === 'arsipkan')>Arsip dinamis — tetap tersedia di sistem</option>
                    <option value="hapus_lembut" @selected(old('action', $policy->action) === 'hapus_lembut')>Soft-delete — dapat dipulihkan admin</option>
                </select>
                @error('action') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-start gap-3 text-sm text-slate-700">
                <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $policy->is_active)) class="mt-0.5 rounded border-slate-300 text-emerald-700">
                <span>Aktifkan kebijakan ini. Kebijakan aktif lainnya untuk alumni akan dinonaktifkan.</span>
            </label>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <a href="{{ route('admin.retention.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Simpan kebijakan</button>
            </div>
        </form>
    </div>
@endsection
