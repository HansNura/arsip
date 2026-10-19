<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::orderBy('nama')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $user = User::create($data);

        AuditLogger::record('CREATE_USER', $user, null, $this->auditPayload($user));

        return redirect()->route('admin.users.index')
            ->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $isSelf = $user->is($request->user());

        if ($isSelf && $data['role'] !== UserRole::Admin->value) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak dapat mengubah peran akun sendiri.',
            ]);
        }

        if ($isSelf && ! $data['is_active']) {
            throw ValidationException::withMessages([
                'is_active' => 'Anda tidak dapat menonaktifkan akun sendiri.',
            ]);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $before = $this->auditPayload($user);
        $user->update($data);

        AuditLogger::record('UPDATE_USER', $user, $before, $this->auditPayload($user->fresh()));

        return redirect()->route('admin.users.index')
            ->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors([
                'user' => 'Anda tidak dapat menonaktifkan akun sendiri.',
            ]);
        }

        $before = $this->auditPayload($user);
        $user->update(['is_active' => ! $user->is_active]);

        AuditLogger::record(
            $user->is_active ? 'ACTIVATE_USER' : 'DEACTIVATE_USER',
            $user,
            $before,
            $this->auditPayload($user->fresh()),
        );

        return back()->with('status', $user->is_active
            ? 'Pengguna berhasil diaktifkan.'
            : 'Pengguna berhasil dinonaktifkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function auditPayload(User $user): array
    {
        return [
            'nama' => $user->nama,
            'email' => $user->email,
            'role' => $user->role->value,
            'is_active' => $user->is_active,
        ];
    }
}
