<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_accounts_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('social_accounts'));
        $this->assertTrue(Schema::hasColumns('social_accounts', [
            'user_id', 'platform', 'url', 'handle', 'followers_count', 'last_synced_at',
        ]));
    }

    public function test_follower_snapshots_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('follower_snapshots'));
        $this->assertTrue(Schema::hasColumns('follower_snapshots', [
            'social_account_id', 'followers_count', 'collected_at', 'source', 'recorded_by',
        ]));
    }

    public function test_users_table_has_categories_and_address_and_keeps_birthdate(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'categories'));
        $this->assertTrue(Schema::hasColumn('users', 'address'));
        $this->assertTrue(Schema::hasColumn('users', 'birthdate'));
    }
}
