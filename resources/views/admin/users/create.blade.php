@extends('layouts.backoffice')

@section('title', 'Tambah Pengguna')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest hover:text-forest-deep">
            <x-icon name="arrow-right" class="size-4 rotate-180" />
            Kembali ke pengguna
        </a>
    </div>

    <x-page-header eyebrow="Administrasi" title="Tambah Pengguna" subtitle="Buat akun baru untuk admin atau staf arsip." />

    <div class="card mt-6 max-w-3xl">
        <div class="card-body">
            @include('admin.users._form', [
                'action' => route('admin.users.store'),
                'method' => 'POST',
                'user' => null,
            ])
        </div>
    </div>
@endsection
