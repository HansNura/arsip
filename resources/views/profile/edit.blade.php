@extends('layouts.backoffice')

@section('title', 'Profil')

@section('content')
    <x-page-header eyebrow="Akun" title="Profil" subtitle="Informasi akun dan pengaturan kata sandi." />

    <div class="mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Informasi akun</h2>
                    <p class="section-subtitle">Detail akun yang sedang digunakan.</p>
                </div>
                <span class="seal seal-sm">{{ collect(explode(' ', trim((string) auth()->user()->nama)))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
            </div>
            <dl class="divide-y divide-ink/10">
                @foreach ([
                    ['Nama', auth()->user()->nama],
                    ['Email', auth()->user()->email],
                    ['Peran', auth()->user()->role === \App\Enums\UserRole::Admin ? 'Admin' : 'Staf Arsip'],
                    ['Login terakhir', auth()->user()->last_login_at?->translatedFormat('d M Y, H:i') ?? 'Belum pernah'],
                ] as [$label, $value])
                    <div class="flex items-start justify-between gap-4 px-5 py-4 sm:px-6">
                        <dt class="text-[0.64rem] font-semibold tracking-[0.12em] text-ink/40 uppercase">{{ $label }}</dt>
                        <dd class="text-right text-sm font-medium text-ink">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Ubah kata sandi</h2>
                    <p class="section-subtitle">Gunakan kata sandi yang kuat dan tidak dipakai di layanan lain.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('profile.password') }}" class="card-body space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="field-label">Kata sandi saat ini</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="field-input">
                    @error('current_password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="field-label">Kata sandi baru</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" class="field-input">
                        @error('password')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="field-label">Konfirmasi kata sandi baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="field-input">
                    </div>
                </div>

                <div class="flex justify-end border-t border-ink/10 pt-5">
                    <button type="submit" class="btn btn-primary">Simpan kata sandi</button>
                </div>
            </form>
        </section>
    </div>
@endsection
