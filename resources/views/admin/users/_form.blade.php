@php($isEdit = $user !== null)

@if ($errors->any())
    <div class="alert alert-danger mb-6">
        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <label class="block sm:col-span-2">
            <span class="field-label">Nama lengkap</span>
            <input id="nama" name="nama" type="text" value="{{ old('nama', $user?->nama) }}" required autofocus class="field-input">
            @error('nama')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </label>

        <label class="block">
            <span class="field-label">Email</span>
            <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required class="field-input">
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </label>

        <label class="block">
            <span class="field-label">Peran</span>
            <select id="role" name="role" required class="field-select">
                @foreach (\App\Enums\UserRole::cases() as $role)
                    <option value="{{ $role->value }}" @selected(old('role', $user?->role?->value ?? \App\Enums\UserRole::StafArsip->value) === $role->value)>
                        {{ $role === \App\Enums\UserRole::Admin ? 'Admin' : 'Staf Arsip' }}
                    </option>
                @endforeach
            </select>
            @error('role')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </label>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <label class="block">
            <span class="field-label">{{ $isEdit ? 'Kata sandi baru (opsional)' : 'Kata sandi' }}</span>
            <input id="password" name="password" type="password" autocomplete="new-password" @required(! $isEdit) class="field-input">
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </label>

        <label class="block">
            <span class="field-label">Konfirmasi kata sandi</span>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $isEdit) class="field-input">
        </label>
    </div>

    <p class="field-hint">Minimal 8 karakter. @if ($isEdit) Biarkan kosong jika kata sandi tidak diubah. @endif</p>

    <label class="flex items-start gap-3 text-sm text-ink/70">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $user?->is_active ?? true)) class="mt-0.5 size-4 rounded border-ink/20 text-forest focus:ring-forest/30">
        <span>
            <span class="block font-medium text-ink">Akun aktif</span>
            <span class="mt-0.5 block text-xs text-ink/45">Akun nonaktif tidak dapat login dan sesinya langsung diputus.</span>
        </span>
    </label>

    <div class="flex items-center justify-end gap-3 border-t border-ink/10 pt-5">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
