<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('roles.manage');

        return view('roles.index', [
            'roles' => Role::query()->withCount('users')->with('permissions')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('roles.manage');

        return view('roles.create', ['permissions' => Permission::query()->orderBy('name')->get()]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorize('roles.manage');

        DB::transaction(function () use ($request): void {
            $role = Role::query()->create(['name' => $request->validated('name'), 'guard_name' => 'web']);
            $permissions = $role->name === 'admin'
                ? Permission::query()->pluck('name')->all()
                : $request->validated('permissions', []);

            $role->syncPermissions($permissions);
        });

        return redirect()->route('roles.index')->with('status', 'Role berhasil dibuat.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('roles.manage');

        return view('roles.edit', [
            'managedRole' => $role->load('permissions'),
            'permissions' => Permission::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('roles.manage');

        DB::transaction(function () use ($request, $role): void {
            if (! in_array($role->name, ['admin', 'employee'], true)) {
                $role->update(['name' => $request->validated('name')]);
            }

            $permissions = $role->name === 'admin'
                ? Permission::query()->pluck('name')->all()
                : $request->validated('permissions', []);

            $role->syncPermissions($permissions);
        });

        return redirect()->route('roles.index')->with('status', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('roles.manage');

        if (in_array($role->name, ['admin', 'employee'], true)) {
            throw ValidationException::withMessages(['role' => 'Role bawaan tidak dapat dihapus.']);
        }

        if ($role->users()->exists()) {
            throw ValidationException::withMessages(['role' => 'Role masih digunakan oleh pengguna.']);
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role berhasil dihapus.');
    }
}
