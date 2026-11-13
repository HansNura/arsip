@extends('layouts.backoffice')

@section('title', 'Edit Pengguna')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest hover:text-forest-deep">
            <x-icon name="arrow-right" class="size-4 rotate-180" />
            Kembali ke pengguna
        </a>
    </div>

    <x-page-header eyebrow="Administrasi" title="Edit Pengguna" subtitle="Perbarui data, peran, atau status akun." />

    <div class="card mt-6 max-w-3xl">
        <div class="card-body">
            @include('admin.users._form', [
                'action' => route('admin.users.update', $user),
                'method' => 'PUT',
                'user' => $user,
            ])
        </div>
    </div>
@endsection
