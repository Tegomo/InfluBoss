<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Content;
use App\Models\Contract;
use App\Models\Note;
use App\Models\Payment;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    private User $influencer;

    private Campaign $campaign;

    private Assignment $assignment;

    private Publication $publication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = User::factory()->create(['role' => 'client', 'name' => 'Orange Cameroun']);
        $this->influencer = User::factory()->influencer()->create(['name' => 'Jane Doe']);
        $this->campaign = Campaign::factory()->create(['name' => 'Campagne Max It', 'platforms' => json_encode(['instagram'])]);
        $this->assignment = Assignment::factory()->create(['campaign_id' => $this->campaign->id, 'influencer_id' => $this->influencer->id]);
        $this->publication = Publication::factory()->create(['assignment_id' => $this->assignment->id, 'influencer_id' => $this->influencer->id]);
        Contract::factory()->create(['influencer_id' => $this->influencer->id, 'payment_terms' => 'Clause confidentielle']);
        Payment::factory()->create(['influencer_id' => $this->influencer->id, 'label' => 'Versement confidentiel']);
    }

    public function test_client_can_view_activities(): void
    {
        $content = Content::create([
            'title' => 'Brief Max It', 'description' => 'Brief', 'campaign_id' => $this->campaign->id,
            'created_by' => User::factory()->admin()->create()->id, 'type' => 'text', 'status' => 'approved',
        ]);

        // Le tableau de bord utilise MONTH()/YEAR() (MySQL) : il est vérifié sur MySQL, pas sous SQLite.
        $this->actingAs($this->client);
        foreach ([
            route('campaigns.index'), route('campaigns.show', $this->campaign),
            route('contents.index'), route('contents.show', $content),
            route('influencers.index'), route('influencers.show', $this->influencer),
            route('assignments.index'), route('assignments.show', $this->assignment),
            route('publications.index'), route('publications.show', $this->publication),
            route('notes.index', $this->assignment),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        // Statistiques : requêtes MySQL (MONTH, YEAR) ; ici on vérifie seulement que l'accès n'est pas refusé.
        $this->assertNotSame(403, $this->get(route('admin.statistics'))->getStatusCode());

        $this->get(route('influencers.index'))->assertSee('Jane Doe');
        $this->get(route('campaigns.index'))->assertSee('Campagne Max It');
    }

    public function test_client_never_sees_contracts_payments_or_legal_documents(): void
    {
        $this->actingAs($this->client);

        $this->get(route('contracts.index'))->assertForbidden();
        $this->get(route('payments.index'))->assertForbidden();

        $this->get(route('influencers.show', $this->influencer))
            ->assertOk()
            ->assertDontSee('href="#documents"', false)
            ->assertDontSee('href="#payments"', false)
            ->assertDontSee('Clause confidentielle')
            ->assertDontSee('Versement confidentiel');
    }

    public function test_client_cannot_open_staff_management(): void
    {
        $this->actingAs($this->client);

        $this->get(route('supervisors.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('contracts.create'))->assertForbidden();
    }

    public function test_client_cannot_write_anything(): void
    {
        $this->actingAs($this->client);

        foreach ([
            route('campaigns.create'), route('campaigns.edit', $this->campaign),
            route('assignments.create'), route('assignments.edit', $this->assignment),
            route('publications.create'), route('influencers.edit', $this->influencer),
            route('notes.create', $this->assignment),
        ] as $url) {
            $this->get($url)->assertForbidden();
        }

        $this->post(route('campaigns.store'), ['name' => 'X'])->assertForbidden();
        $this->put(route('campaigns.update', $this->campaign), ['name' => 'X'])->assertForbidden();
        $this->delete(route('assignments.destroy', $this->assignment))->assertForbidden();
        $this->post(route('notes.store', $this->assignment), ['content' => 'Commentaire'])->assertForbidden();
        $this->post(route('publications.verify', $this->publication), ['action' => 'verify'])->assertForbidden();
        $this->post(route('influencers.update-followers', $this->influencer), ['followers' => ['instagram' => 10]])->assertForbidden();

        $this->assertSame('Campagne Max It', $this->campaign->fresh()->name);
        $this->assertNotSoftDeleted($this->assignment);
        $this->assertSame(0, Note::count());
    }

    public function test_client_can_log_out_and_mark_notifications_read(): void
    {
        $this->actingAs($this->client)->post(route('notifications.read-all'))->assertRedirect();
        $this->actingAs($this->client)->post(route('logout'))->assertRedirect();
        $this->assertGuest();
    }

    public function test_client_menu(): void
    {
        $this->actingAs($this->client)->get(route('campaigns.index'))
            ->assertOk()
            ->assertSee('<p>Ambassadeurs</p>', false)
            ->assertSee('<p>Statistiques</p>', false)
            ->assertSee('<p>Campagnes</p>', false)
            ->assertDontSee('<p>Contrats</p>', false)
            ->assertDontSee('<p>Paiements</p>', false)
            ->assertDontSee('<p>Utilisateurs</p>', false)
            ->assertDontSee('<p>Superviseurs</p>', false);
    }

    public function test_client_does_not_see_creation_buttons_on_main_lists(): void
    {
        $this->actingAs($this->client);

        $this->get(route('campaigns.index'))->assertDontSee(route('campaigns.create'), false);
        $this->get(route('assignments.index'))->assertDontSee(route('assignments.create'), false);
        $this->get(route('publications.index'))->assertDontSee(route('publications.create'), false);
        $this->get(route('influencers.index'))->assertDontSee(route('influencers.create'), false);
    }
}
