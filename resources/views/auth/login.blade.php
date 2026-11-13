@extends('layouts.public')

@section('title', 'Masuk')

@section('content')
    <section class="page-shell py-10 lg:py-16">
        <div class="mx-auto grid max-w-5xl overflow-hidden rounded-[2rem] border border-ink/10 bg-paper shadow-[0_30px_80px_-50px_rgba(16,35,28,0.4)] lg:grid-cols-[1.02fr_0.98fr]">
            <aside class="sidebar relative hidden flex-col justify-between p-10 text-paper lg:flex">
                <div>
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="seal seal-sm">eA</span>
                        <span class="font-display text-xl font-semibold tracking-[-0.02em] text-white">e-ARSIP</span>
                    </a>
                    <p class="mt-12 text-[0.64rem] font-semibold tracking-[0.2em] text-brass uppercase">Ruang kerja arsip</p>
                    <p class="mt-3 font-display text-3xl leading-[1.1] font-semibold tracking-[-0.03em] text-white">
                        Satu tempat untuk data, sertifikat, dan jejak audit.
                    </p>
                    <p class="mt-4 text-sm leading-6 text-paper/55">
                        Masuk untuk mengelola peserta, menerbitkan sertifikat, dan menindaklanjuti permohonan penerbitan ulang.
                    </p>
                </div>

                <ul class="mt-12 space-y-4 border-t border-white/10 pt-6 text-sm text-paper/65">
                    @foreach ([
                        'Akses berbasis peran admin dan staf arsip.',
                        'Setiap aksi penting tercatat di audit log.',
                        'Verifikasi publik tetap terbuka tanpa login.',
                    ] as $item)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full border border-brass/40 text-brass">
                                <x-icon name="check" class="size-3" />
                            </span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </aside>

            <div class="p-6 sm:p-10">
                <div class="mx-auto max-w-md">
                    <p class="eyebrow">Akses staf</p>
                    <h1 class="mt-2 font-display text-3xl font-semibold tracking-[-0.03em] text-ink">Masuk ke e-ARSIP</h1>
                    <p class="mt-2 text-sm leading-6 text-ink/55">Gunakan akun staf yang telah terdaftar.</p>

                    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="field-label">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                   class="field-input">
                            @error('email')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="field-label">Kata sandi</label>
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                   class="field-input">
                            @error('password')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-2.5 text-sm text-ink/60">
                            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-ink/20 text-forest focus:ring-forest/30">
                            Ingat saya
                        </label>

                        <button type="submit" class="btn btn-primary w-full">
                            Masuk
                            <x-icon name="arrow-right" class="size-4" />
                        </button>
                    </form>

                    @if (app()->environment('local'))
                        <div class="mt-7 rounded-2xl border border-ink/10 bg-chalk p-4 text-sm text-ink/60">
                            <p class="text-[0.62rem] font-semibold tracking-[0.14em] text-ink/40 uppercase">Akun demo lokal</p>
                            <div class="mt-3 grid gap-2">
                                <p>Admin <span class="font-mono text-ink">admin@arsip.test</span> / <span class="font-mono text-ink">password</span></p>
                                <p>Staf <span class="font-mono text-ink">staf@arsip.test</span> / <span class="font-mono text-ink">password</span></p>
                            </div>
                        </div>
                    @endif

                    <p class="mt-6 text-center text-xs text-ink/40">
                        <a href="{{ route('home') }}" class="hover:text-forest">Kembali ke beranda</a>
                        <span class="mx-2">·</span>
                        <a href="{{ route('verify.index') }}" class="hover:text-forest">Verifikasi publik</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
