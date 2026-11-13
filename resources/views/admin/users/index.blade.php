@extends('layouts.backoffice')

@section('title', 'Manajemen Pengguna')

@section('content')
    <x-page-header eyebrow="Administrasi" title="Manajemen Pengguna" subtitle="Kelola akun admin dan staf arsip.">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="size-4" />
            Tambah pengguna
        </a>
    </x-page-header>

    @if ($errors->has('user'))
        <div class="alert alert-danger mt-6">{{ $errors->first('user') }}</div>
    @endif

    <div class="table-shell mt-6">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Login terakhir</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <span class="font-semibold text-ink">{{ $user->nama }}</span>
                                @if ($user->is(auth()->user()))
                                    <span class="ml-2 text-xs text-ink/40">(Anda)</span>
                                @endif
                            </td>
                            <td><span class="font-mono text-xs">{{ $user->email }}</span></td>
                            <td><span @class(['badge', 'badge-info' => $user->role === \App\Enums\UserRole::Admin, 'badge-neutral' => $user->role !== \App\Enums\UserRole::Admin])>{{ $user->role === \App\Enums\UserRole::Admin ? 'Admin' : 'Staf Arsip' }}</span></td>
                            <td><span @class(['badge', 'badge-valid' => $user->is_active, 'badge-neutral' => ! $user->is_active])>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="font-mono text-xs">{{ $user->last_login_at?->translatedFormat('d M Y, H:i') ?? 'Belum pernah' }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm">Edit</a>
                                    @if (! $user->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" @class(['btn btn-sm', 'btn-danger' => $user->is_active, 'btn-secondary' => ! $user->is_active])>
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-empty">
                                <x-empty-state icon="users" title="Belum ada pengguna" description="Tambahkan akun admin atau staf arsip.">
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Tambah pengguna</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-ink/10 px-5 py-4">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
