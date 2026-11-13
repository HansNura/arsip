@extends('layouts.backoffice')

@section('title', $policy->exists ? 'Edit Kebijakan Retensi' : 'Tambah Kebijakan Retensi')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.retention.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest hover:text-forest-deep">
            <x-icon name="arrow-right" class="size-4 rotate-180" />
            Kembali ke retensi
        </a>
    </div>

    <x-page-header eyebrow="Administrasi" :title="$policy->exists ? 'Edit Kebijakan Retensi' : 'Tambah Kebijakan Retensi'"
                  subtitle="Kebijakan berlaku untuk peserta lulus yang seluruh pelatihannya selesai lebih dari masa retensi." />

    @if ($errors->any())
        <div class="alert alert-danger mt-6">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $policy->exists ? route('admin.retention.update', $policy) : route('admin.retention.store') }}" class="card mt-6 max-w-3xl">
        @csrf
        @if ($policy->exists)
            @method('PUT')
        @endif

        <div class="card-header">
            <div>
                <h2 class="section-title">Detail kebijakan</h2>
                <p class="section-subtitle">Hanya satu kebijakan aktif untuk alumni pada satu waktu.</p>
            </div>
        </div>

        <div class="card-body space-y-5">
            <label class="block">
                <span class="field-label">Nama kebijakan</span>
                <input id="nama" name="nama" type="text" value="{{ old('nama', $policy->nama) }}" required maxlength="120" class="field-input">
                @error('nama') <p class="field-error">{{ $message }}</p> @enderror
            </label>

            <label class="block">
                <span class="field-label">Masa retensi (tahun)</span>
                <input id="retention_years" name="retention_years" type="number" min="1" max="100" value="{{ old('retention_years', $policy->retention_years) }}" required class="field-input font-mono">
                @error('retention_years') <p class="field-error">{{ $message }}</p> @enderror
            </label>

            <label class="block">
                <span class="field-label">Tindakan setelah masa retensi</span>
                <select id="action" name="action" required class="field-select">
                    <option value="cold_storage" @selected(old('action', $policy->action) === 'cold_storage')>Arsip statis — penyimpanan dingin</option>
                    <option value="arsipkan" @selected(old('action', $policy->action) === 'arsipkan')>Arsip dinamis — tetap tersedia di sistem</option>
                    <option value="hapus_lembut" @selected(old('action', $policy->action) === 'hapus_lembut')>Soft-delete — dapat dipulihkan admin</option>
                </select>
                @error('action') <p class="field-error">{{ $message }}</p> @enderror
            </label>

            <label class="flex items-start gap-3 text-sm text-ink/70">
                <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $policy->is_active)) class="mt-0.5 size-4 rounded border-ink/20 text-forest focus:ring-forest/30">
                <span>
                    <span class="block font-medium text-ink">Aktifkan kebijakan ini</span>
                    <span class="mt-0.5 block text-xs text-ink/45">Kebijakan aktif lainnya untuk alumni akan dinonaktifkan.</span>
                </span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-ink/10 px-5 py-4 sm:px-6">
            <a href="{{ route('admin.retention.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan kebijakan</button>
        </div>
    </form>
@endsection
