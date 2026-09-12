<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $client = Client::factory()->create();

        $this->patch("/clients/{$client->id}", ['name' => 'x', 'industry_category' => 'y'])->assertRedirect('/login');
        $this->delete("/clients/{$client->id}")->assertRedirect('/login');
    }

    public function test_a_client_can_be_updated(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['name' => 'Old Name', 'industry_category' => 'Old Category']);

        $response = $this->actingAs($user)->patch("/clients/{$client->id}", [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
        ]);

        $response->assertRedirect(route('keywords.index'));
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
        ]);
    }

    public function test_updating_a_client_redirects_based_on_its_own_stored_type_not_client_input(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_NEGATIVE_KEYWORDS]);

        // No "context" is sent at all — the redirect must come from the
        // client's own stored type, since trusting a client-submitted
        // value here would let a mismatched request send you to the
        // wrong index page.
        $response = $this->actingAs($user)->patch("/clients/{$client->id}", [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
        ]);

        $response->assertRedirect(route('negative-keywords.index'));
    }

    public function test_updating_a_client_does_not_change_its_type(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_LOCATION]);

        $this->actingAs($user)->patch("/clients/{$client->id}", [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
            'type' => Client::TYPE_KEYWORDS,
        ]);

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'type' => Client::TYPE_LOCATION]);
    }

    public function test_updating_a_client_requires_name_and_category(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($user)->patch("/clients/{$client->id}", []);

        $response->assertSessionHasErrors(['name', 'industry_category']);
    }

    public function test_updating_a_nonexistent_client_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/clients/99999', [
            'name' => 'Acme',
            'industry_category' => 'Home Services',
        ])->assertNotFound();
    }

    public function test_deleting_a_client_removes_it_and_redirects_based_on_its_stored_type(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_LOCATION]);

        $response = $this->actingAs($user)->delete("/clients/{$client->id}");

        $response->assertRedirect(route('locations.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_deleting_a_client_cascades_to_its_child_records(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->keywords()->create(['keyword' => 'plumber', 'match_type' => 'Broad']);

        $this->actingAs($user)->delete("/clients/{$client->id}");

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
        $this->assertDatabaseMissing('keywords', ['client_id' => $client->id]);
    }

    public function test_deleting_a_client_in_one_section_does_not_affect_a_different_client_in_another_section(): void
    {
        $user = User::factory()->create();
        $keywordsClient = Client::factory()->create(['name' => 'Acme Plumbing', 'type' => Client::TYPE_KEYWORDS]);
        $negativeClient = Client::factory()->create(['name' => 'Acme Plumbing', 'type' => Client::TYPE_NEGATIVE_KEYWORDS]);

        $this->actingAs($user)->delete("/clients/{$keywordsClient->id}");

        $this->assertDatabaseMissing('clients', ['id' => $keywordsClient->id]);
        $this->assertDatabaseHas('clients', ['id' => $negativeClient->id]);
    }

    public function test_deleting_a_nonexistent_client_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/clients/99999')->assertNotFound();
    }
}
