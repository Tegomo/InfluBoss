<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractAlert;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContractExpiryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $superAdmin;

    private User $linkedSupervisor;

    private User $otherSupervisor;

    private User $influencer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->superAdmin = User::factory()->superAdmin()->create();
        $this->linkedSupervisor = User::factory()->supervisor()->create();
        $this->otherSupervisor = User::factory()->supervisor()->create();
        $this->influencer = User::factory()->influencer()->create(['name' => 'Jane Doe']);
        $this->linkedSupervisor->influencers()->attach($this->influencer->id);
    }

    private function contractEndingIn(int $days, string $status = 'active'): Contract
    {
        return Contract::factory()->create([
            'influencer_id' => $this->influencer->id,
            'status' => $status,
            'end_date' => now()->addDays($days)->toDateString(),
        ]);
    }

    private function sentThresholds(Contract $contract): array
    {
        return $contract->alerts()->orderBy('days_before')->pluck('days_before')->all();
    }

    public function test_alert_goes_to_active_admins_only(): void
    {
        Notification::fake();
        $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);
        $contract = $this->contractEndingIn(25);

        $this->artisan('contracts:check-expirations')->assertSuccessful();

        Notification::assertSentTo(
            [$this->admin, $this->superAdmin],
            ContractExpiringNotification::class,
            fn ($notification) => $notification->contract->is($contract) && $notification->daysRemaining === 25
        );
        // Les contrats sont réservés aux admins : superviseurs et ambassadeur ne sont pas prévenus.
        Notification::assertNotSentTo(
            [$this->linkedSupervisor, $this->otherSupervisor, $this->influencer, $inactiveAdmin],
            ContractExpiringNotification::class
        );
        $this->assertSame([30], $this->sentThresholds($contract));
    }

    public function test_alert_is_not_sent_twice_for_the_same_threshold(): void
    {
        Notification::fake();
        $this->contractEndingIn(20);

        $this->artisan('contracts:check-expirations');
        $this->artisan('contracts:check-expirations');

        Notification::assertSentToTimes($this->admin, ContractExpiringNotification::class, 1);
    }

    public function test_missed_runs_send_only_the_most_urgent_alert(): void
    {
        Notification::fake();
        $contract = $this->contractEndingIn(5);

        $this->artisan('contracts:check-expirations');

        Notification::assertSentToTimes($this->admin, ContractExpiringNotification::class, 1);
        $this->assertSame([7, 15, 30], $this->sentThresholds($contract));
    }

    public function test_next_threshold_is_sent_when_reached(): void
    {
        Notification::fake();
        $contract = $this->contractEndingIn(14);
        ContractAlert::create(['contract_id' => $contract->id, 'days_before' => 30, 'sent_at' => now()->subDays(16)]);

        $this->artisan('contracts:check-expirations');

        Notification::assertSentToTimes($this->admin, ContractExpiringNotification::class, 1);
        $this->assertSame([15, 30], $this->sentThresholds($contract));
    }

    public function test_contracts_outside_window_or_not_active_are_ignored(): void
    {
        Notification::fake();
        $this->contractEndingIn(45);
        $this->contractEndingIn(10, 'negotiation');
        $this->contractEndingIn(10, 'terminated');

        $this->artisan('contracts:check-expirations');

        Notification::assertNothingSent();
        $this->assertDatabaseCount('contract_alerts', 0);
    }

    public function test_active_contracts_past_their_end_date_become_expired(): void
    {
        Notification::fake();
        $past = $this->contractEndingIn(-1);
        $today = $this->contractEndingIn(0);
        $terminated = $this->contractEndingIn(-3, 'terminated');

        $this->artisan('contracts:check-expirations')
            ->expectsOutputToContain('1 contrat(s) passé(s) en « Expiré »')
            ->assertSuccessful();

        $this->assertSame('expired', $past->fresh()->status);
        $this->assertSame('active', $today->fresh()->status, 'un contrat qui finit aujourd\'hui reste actif');
        $this->assertSame('terminated', $terminated->fresh()->status);
    }

    public function test_notification_is_stored_in_database_and_renders_mail(): void
    {
        $contract = $this->contractEndingIn(7);

        $this->artisan('contracts:check-expirations');

        $stored = $this->admin->notifications()->first();
        $this->assertNotNull($stored);
        $this->assertSame(route('contracts.show', $contract), $stored->data['url']);
        $this->assertStringContainsString('Jane Doe', $stored->data['message']);
        $this->assertStringContainsString('7 jours', $stored->data['message']);

        $mail = (new ContractExpiringNotification($contract, 7))->toMail($this->admin);
        $this->assertStringContainsString('Jane Doe', $mail->subject);
        $this->assertSame(route('contracts.show', $contract), $mail->actionUrl);
    }

    public function test_command_is_scheduled_daily(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('contracts:check-expirations')
            ->assertSuccessful();
    }
}
