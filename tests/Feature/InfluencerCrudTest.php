<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InfluencerCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'pseudo' => 'janedoe',
            'phone' => '+237600000000',
            'address' => 'Akwa, rue de la Joie',
            'city' => 'Douala',
            'categories' => ['business', 'story_tellers'],
            'bio' => 'Créatrice tech',
            'social' => [
                'instagram' => ['url' => 'https://www.instagram.com/janedoe', 'followers' => '1500'],
                'tiktok' => ['url' => 'https://www.tiktok.com/@janedoe', 'followers' => ''],
            ],
        ], $overrides);
    }

    public function test_admin_can_create_influencer_with_categories_and_social_accounts(): void
    {
        $response = $this->actingAs($this->admin)->post(route('influencers.store'), $this->payload());

        $influencer = User::where('email', 'jane@example.com')->firstOrFail();
        $response->assertRedirect(route('influencers.show', $influencer));

        $this->assertSame('influencer', $influencer->role);
        $this->assertSame('janedoe', $influencer->pseudo);
        $this->assertSame('Douala', $influencer->city);
        $this->assertSame('Akwa, rue de la Joie', $influencer->address);
        $this->assertSame(['business', 'story_tellers'], $influencer->categories);
        $this->assertNull($influencer->birthdate);
        $this->assertNull($influencer->social_links);
        $this->assertTrue($influencer->is_active);
        $this->assertTrue(Hash::check('Pass@2026', $influencer->password), 'mot de passe par défaut');

        $accounts = $influencer->socialAccounts()->get()->keyBy('platform');
        $this->assertCount(2, $accounts);
        $this->assertSame(1500, $accounts['instagram']->followers_count);
        $this->assertSame('janedoe', $accounts['tiktok']->handle);
        $this->assertDatabaseHas('follower_snapshots', [
            'social_account_id' => $accounts['instagram']->id,
            'followers_count' => 1500,
            'source' => 'manual',
            'recorded_by' => $this->admin->id,
        ]);
    }

    public function test_create_rejects_unknown_category(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('influencers.create'))
            ->post(route('influencers.store'), $this->payload(['categories' => ['zzz']]));

        $response->assertSessionHasErrors('categories.0');
        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
    }

    public function test_create_rejects_unknown_platform(): void
    {
        $payload = $this->payload();
        $payload['social']['pinterest'] = ['url' => 'https://pinterest.com/x', 'followers' => ''];

        $response = $this->actingAs($this->admin)->post(route('influencers.store'), $payload);

        $response->assertSessionHasErrors('social');
    }

    public function test_create_rejects_invalid_followers(): void
    {
        $payload = $this->payload(['social' => ['instagram' => ['followers' => '-5']]]);

        $response = $this->actingAs($this->admin)->post(route('influencers.store'), $payload);

        $response->assertSessionHasErrors('social.instagram.followers');
    }

    public function test_update_with_unchecked_is_active_deactivates(): void
    {
        $influencer = User::factory()->influencer()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->put(route('influencers.update', $influencer), [
            'name' => $influencer->name,
            'email' => $influencer->email,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('influencers.show', $influencer));
        $this->assertFalse($influencer->fresh()->is_active);
    }

    public function test_update_replaces_old_photo(): void
    {
        Storage::disk('public')->put('profile-photos/old.jpg', 'old');
        $influencer = User::factory()->influencer()->create(['profile_photo' => 'profile-photos/old.jpg']);

        $this->actingAs($this->admin)->put(route('influencers.update', $influencer), [
            'name' => $influencer->name,
            'email' => $influencer->email,
            'is_active' => '1',
            'profile_photo' => UploadedFile::fake()->image('new.jpg'),
        ])->assertSessionHasNoErrors();

        $newPath = $influencer->fresh()->profile_photo;
        $this->assertNotSame('profile-photos/old.jpg', $newPath);
        Storage::disk('public')->assertMissing('profile-photos/old.jpg');
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_update_clearing_url_removes_social_account(): void
    {
        $influencer = User::factory()->influencer()->create();
        SocialAccount::factory()->create(['user_id' => $influencer->id, 'platform' => 'instagram'])->recordFollowers(100);

        $this->actingAs($this->admin)->put(route('influencers.update', $influencer), [
            'name' => $influencer->name,
            'email' => $influencer->email,
            'is_active' => '1',
            'social' => ['instagram' => ['url' => '', 'followers' => '']],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('social_accounts', 0);
        $this->assertDatabaseCount('follower_snapshots', 0);
    }

    public function test_update_keeps_birthdate_and_saves_categories_and_address(): void
    {
        $influencer = User::factory()->influencer()->create(['birthdate' => '1990-01-15']);

        $this->actingAs($this->admin)->put(route('influencers.update', $influencer), [
            'name' => $influencer->name,
            'email' => $influencer->email,
            'is_active' => '1',
            'address' => 'Bonapriso',
            'categories' => ['noso'],
        ])->assertSessionHasNoErrors();

        $fresh = $influencer->fresh();
        $this->assertSame('1990-01-15', $fresh->birthdate->format('Y-m-d'));
        $this->assertSame('Bonapriso', $fresh->address);
        $this->assertSame(['noso'], $fresh->categories);
    }

    public function test_removed_assignment_and_dashboard_routes_return_404(): void
    {
        $influencer = User::factory()->influencer()->create();

        $this->actingAs($this->admin)->get("/influencers/{$influencer->id}/assign-campaigns")->assertNotFound();
        $this->actingAs($this->admin)->post("/influencers/{$influencer->id}/store-assignment")->assertNotFound();
        $this->actingAs($this->admin)->get("/influencers/{$influencer->id}/dashboard")->assertNotFound();
    }

    public function test_supervisor_cannot_access_create_form(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $this->actingAs($supervisor)->get(route('influencers.create'))->assertForbidden();
    }

    public function test_create_and_edit_forms_render_without_birthdate(): void
    {
        $influencer = User::factory()->influencer()->create();

        $this->actingAs($this->admin)->get(route('influencers.create'))
            ->assertOk()
            ->assertSee('Catégories')
            ->assertSee('name="address"', false)
            ->assertDontSee('name="birthdate"', false)
            ->assertDontSee('name="password"', false)
            ->assertDontSee('name="password_confirmation"', false)
            ->assertSee('Pass@2026');

        $this->actingAs($this->admin)->get(route('influencers.edit', $influencer))
            ->assertOk()
            ->assertSee('name="social[instagram][url]"', false)
            ->assertSee('name="social[instagram][followers]"', false)
            ->assertDontSee('name="birthdate"', false);
    }
}
