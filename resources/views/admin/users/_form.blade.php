@php($isEdit = $user !== null)

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="nama" class="block text-sm font-medium text-slate-700">Nama lengkap</label>
        <input id="nama" name="nama" type="text" value="{{ old('nama', $user?->nama) }}" required autofocus
               class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
        @error('nama')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required
               class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
        @error('email')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="role" class="block text-sm font-medium text-slate-700">Peran</label>
        <select id="role" name="role" required
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
            @foreach (\App\Enums\UserRole::cases() as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user?->role?->value ?? \App\Enums\UserRole::StafArsip->value) === $role->value)>
                    {{ $role === \App\Enums\UserRole::Admin ? 'Admin' : 'Staf Arsip' }}
                </option>
            @endforeach
        </select>
        @error('role')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">
                {{ $isEdit ? 'Kata sandi baru (opsional)' : 'Kata sandi' }}
            </label>
            <input id="password" name="password" type="password" autocomplete="new-password" @required(! $isEdit)
                   class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
            @error('password')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $isEdit)
                   class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
        </div>
    </div>

    <p class="text-xs text-slate-500">
        Minimal 8 karakter.
        @if ($isEdit)
            Biarkan kosong jika kata sandi tidak diubah.
        @endif
    </p>

    <div>
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $user?->is_active ?? true))
                   class="size-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500">
            Akun aktif
        </label>
        @error('is_active')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
        <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
            Batal
        </a>
        <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
            Simpan
        </button>
    </div>
</form>
