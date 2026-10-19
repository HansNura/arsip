@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Tambah Pengguna</h1>
            <p class="mt-2 text-sm text-slate-600">Buat akun baru untuk admin atau staf arsip.</p>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
            @include('admin.users._form', [
                'action' => route('admin.users.store'),
                'method' => 'POST',
                'user' => null,
            ])
        </div>
    </div>
@endsection
