@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Edit Pengguna</h1>
            <p class="mt-2 text-sm text-slate-600">Perbarui data, peran, atau status akun.</p>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
            @include('admin.users._form', [
                'action' => route('admin.users.update', $user),
                'method' => 'PUT',
                'user' => $user,
            ])
        </div>
    </div>
@endsection
