<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('users.manage');

        return view('users.index', [
            'users' => User::query()->with('roles')->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        $this->authorize('users.manage');

        return view('users.create', ['roles' => Role::query()->orderBy('name')->get()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('users.manage');

        DB::transaction(function () use ($request): void {
            $user = User::query()->create([
                ...$request->safe()->only(['name', 'email', 'password']),
                'email_verified_at' => now(),
            ]);
            $user->assignRole($request->validated('role'));
        });

        return redirect()->route('users.index')->with('status', 'Pengguna berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $this->authorize('users.manage');

        return view('users.edit', [
            'managedUser' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('users.manage');

        if ($user->hasRole('admin') && $request->validated('role') !== 'admin' && User::role('admin')->count() === 1) {
            throw ValidationException::withMessages(['role' => 'Admin terakhir tidak dapat dipindahkan ke role lain.']);
        }

        DB::transaction(function () use ($request, $user): void {
            $data = $request->safe()->only(['name', 'email']);

            if ($request->filled('password')) {
                $data['password'] = $request->validated('password');
            }

            $user->update($data);
            $user->syncRoles([$request->validated('role')]);
        });

        return redirect()->route('users.index')->with('status', 'Pengguna berhasil diperbarui.');
    }
}
