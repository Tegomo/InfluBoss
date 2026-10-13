<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignBrandCharterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function charter(): array
    {
        return [
            'products' => 'Forfaits Max It, Orange Money',
            'brand_tone' => 'Chaleureux, inclusif, sans jargon',
            'visual_guidelines' => 'Logo Orange visible, fond clair',
            'required_hashtags' => 'OrangeCameroun, #4GPourTous #OrangeCameroun',
            'required_mentions' => 'orangecameroun @orangemoneycm',
        ];
    }

    public function test_admin_creates_campaign_with_brand_charter(): void
    {
        $this->actingAs($this->admin)->post(route('campaigns.store'), array_merge([
            'name' => 'Campagne Max It',
            'description' => 'Lancement',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'platforms' => ['instagram', 'tiktok'],
        ], $this->charter()))->assertSessionHasNoErrors();

        $campaign = Campaign::where('name', 'Campagne Max It')->firstOrFail();
        $this->assertSame('Forfaits Max It, Orange Money', $campaign->products);
        $this->assertSame('Chaleureux, inclusif, sans jargon', $campaign->brand_tone);
        $this->assertSame(['#OrangeCameroun', '#4GPourTous'], $campaign->required_hashtags);
        $this->assertSame(['@orangecameroun', '@orangemoneycm'], $campaign->required_mentions);
    }

    public function test_admin_updates_brand_charter(): void
    {
        $campaign = Campaign::factory()->create(['platforms' => json_encode(['instagram'])]);

        $this->actingAs($this->admin)->put(route('campaigns.update', $campaign), array_merge([
            'name' => $campaign->name,
            'description' => $campaign->description,
            'start_date' => $campaign->start_date->toDateString(),
            'end_date' => $campaign->end_date->toDateString(),
            'status' => 'active',
            'platforms' => ['instagram'],
        ], $this->charter(), ['required_mentions' => '']))->assertSessionHasNoErrors();

        $campaign->refresh();
        $this->assertSame(['#OrangeCameroun', '#4GPourTous'], $campaign->required_hashtags);
        $this->assertSame([], $campaign->required_mentions);
    }

    public function test_forms_and_show_page_include_the_charter(): void
    {
        $campaign = Campaign::factory()->create([
            'platforms' => json_encode(['instagram']),
            'required_hashtags' => ['#OrangeCameroun'],
            'brand_tone' => 'Chaleureux',
        ]);

        $this->actingAs($this->admin)->get(route('campaigns.create'))
            ->assertOk()->assertSee('name="required_hashtags"', false)->assertSee('name="products"', false);

        $this->actingAs($this->admin)->get(route('campaigns.edit', $campaign))
            ->assertOk()->assertSee('value="#OrangeCameroun"', false);

        $this->actingAs($this->admin)->get(route('campaigns.show', $campaign))
            ->assertOk()->assertSee('Charte de marque')->assertSee('#OrangeCameroun')->assertSee('Chaleureux');
    }
}
