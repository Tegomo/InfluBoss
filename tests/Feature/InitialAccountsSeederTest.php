<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\InitialAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InitialAccountsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_the_initial_accounts(): void
    {
        $this->seed(InitialAccountsSeeder::class);

        $this->assertSame(1, User::where('role', 'super_admin')->count());
        $this->assertSame(1, User::where('role', 'client')->count());
        $this->assertSame(2, User::where('role', 'supervisor')->count());
        $this->assertSame(4, User::count());
        $this->assertTrue(User::where('is_active', true)->count() === 4);
    }

    public function test_is_idempotent_and_keeps_existing_passwords(): void
    {
        $this->seed(InitialAccountsSeeder::class);
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $admin->update(['password' => Hash::make('MotDePasseChoisi1!')]);

        $this->seed(InitialAccountsSeeder::class);

        $this->assertSame(4, User::count());
        $this->assertTrue(Hash::check('MotDePasseChoisi1!', $admin->fresh()->password));
    }

    public function test_generated_passwords_are_strong_and_distinct(): void
    {
        $seeder = new InitialAccountsSeeder;
        $created = $seeder->createAccounts();

        $this->assertCount(4, $created);
        $passwords = array_column($created, 'password');
        $this->assertCount(4, array_unique($passwords));
        foreach ($created as $account) {
            $this->assertGreaterThanOrEqual(14, strlen($account['password']));
            $user = User::where('email', $account['email'])->firstOrFail();
            $this->assertTrue(Hash::check($account['password'], $user->password));
        }
    }

    public function test_default_database_seeder_only_creates_initial_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(4, User::count());
        $this->assertSame(0, User::where('role', 'influencer')->count());
    }
}
