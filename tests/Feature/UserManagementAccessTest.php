<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    private function newUserPayload(string $role): array
    {
        return [
            'name' => 'Nouveau compte',
            'email' => 'nouveau@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'role' => $role,
        ];
    }

    public function test_non_admins_cannot_manage_users(): void
    {
        $target = User::factory()->supervisor()->create();

        foreach (['influencer', 'supervisor', 'client'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user);

            $this->get(route('users.index'))->assertForbidden();
            $this->get(route('users.all'))->assertForbidden();
            $this->get(route('users.create'))->assertForbidden();
            $this->get(route('users.edit', $target))->assertForbidden();
            $this->post(route('users.store'), $this->newUserPayload('admin'))->assertForbidden();
            $this->delete(route('users.destroy', $target))->assertForbidden();
        }

        $this->assertDatabaseMissing('users', ['email' => 'nouveau@example.com']);
        $this->assertNotSoftDeleted($target);
    }

    public function test_admin_can_list_users_and_create_a_client_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('users.index'))->assertOk();
        $this->actingAs($admin)->get(route('users.create'))->assertOk()->assertSee('value="client"', false);

        $this->actingAs($admin)->post(route('users.store'), $this->newUserPayload('client'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'nouveau@example.com', 'role' => 'client']);
    }
}
