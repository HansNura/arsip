@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Profil</h1>
            <p class="mt-2 text-sm text-slate-600">Informasi akun dan pengaturan kata sandi.</p>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">Informasi akun</h2>
            <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-slate-500">Nama</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ auth()->user()->nama }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Email</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ auth()->user()->email }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Peran</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">
                        {{ auth()->user()->role === \App\Enums\UserRole::Admin ? 'Admin' : 'Staf Arsip' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Login terakhir</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">
                        {{ auth()->user()->last_login_at?->translatedFormat('d M Y, H:i') ?? 'Belum pernah' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">Ubah kata sandi</h2>
            <p class="mt-1 text-sm text-slate-600">Gunakan kata sandi yang kuat dan tidak dipakai di layanan lain.</p>

            <form method="POST" action="{{ route('profile.password') }}" class="mt-5 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700">Kata sandi saat ini</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                           class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                    @error('current_password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Kata sandi baru</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                               class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        @error('password')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi kata sandi baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                               class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                        Simpan kata sandi
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
