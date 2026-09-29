<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_home_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_admin_can_create_user_and_assign_role(): void
    {
        $admin = User::factory()->create();
        $this->seed(RolePermissionSeeder::class);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Pegawai Baru',
            'email' => 'pegawai@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee',
        ]);

        $response->assertRedirect(route('users.index'));
        $user = User::query()->where('email', 'pegawai@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('employee'));
    }

    public function test_employee_cannot_access_user_or_role_management(): void
    {
        User::factory()->create();
        $this->seed(RolePermissionSeeder::class);
        $employee = User::factory()->create();
        $employee->assignRole('employee');

        $this->actingAs($employee)->get(route('users.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('roles.index'))->assertForbidden();
    }

    public function test_last_admin_cannot_remove_their_admin_role(): void
    {
        $admin = User::factory()->create();
        $this->seed(RolePermissionSeeder::class);

        $response = $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'employee',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }
}
