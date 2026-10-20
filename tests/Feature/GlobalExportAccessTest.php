<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalExportAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_download_the_global_export(): void
    {
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);

        $this->actingAs($client)->get(route('admin.export-all'))->assertForbidden();
        $this->actingAs(User::factory()->supervisor()->create())->get(route('admin.export-all'))->assertForbidden();
        $this->actingAs(User::factory()->influencer()->create())->get(route('admin.export-all'))->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.export-all'))->assertOk();
    }
}
