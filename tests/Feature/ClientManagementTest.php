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

        $this->patch("/clients/{$client->id}", ['context' => 'keywords'])->assertRedirect('/login');
        $this->delete("/clients/{$client->id}", ['context' => 'keywords'])->assertRedirect('/login');
    }

    public function test_a_client_can_be_updated(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['name' => 'Old Name', 'industry_category' => 'Old Category']);

        $response = $this->actingAs($user)->patch("/clients/{$client->id}", [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
            'context' => 'keywords',
        ]);

        $response->assertRedirect(route('keywords.index'));
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
        ]);
    }

    public function test_updating_a_client_redirects_to_the_correct_context(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($user)->patch("/clients/{$client->id}", [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
            'context' => 'negative-keywords',
        ]);

        $response->assertRedirect(route('negative-keywords.index'));
    }

    public function test_updating_a_client_requires_name_and_category(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($user)->patch("/clients/{$client->id}", ['context' => 'keywords']);

        $response->assertSessionHasErrors(['name', 'industry_category']);
    }

    public function test_updating_a_nonexistent_client_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/clients/99999', [
            'name' => 'Acme',
            'industry_category' => 'Home Services',
            'context' => 'keywords',
        ])->assertNotFound();
    }

    public function test_deleting_a_client_removes_it_and_redirects_to_the_correct_context(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($user)->delete("/clients/{$client->id}", ['context' => 'negative-keywords']);

        $response->assertRedirect(route('negative-keywords.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_deleting_a_client_also_deletes_its_keywords_and_negative_keywords(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->keywords()->create(['keyword' => 'plumber', 'match_type' => 'Broad']);
        $client->negativeKeywords()->create(['keyword' => 'free', 'match_type' => 'Broad']);

        $this->actingAs($user)->delete("/clients/{$client->id}", ['context' => 'keywords']);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
        $this->assertDatabaseMissing('keywords', ['client_id' => $client->id]);
        $this->assertDatabaseMissing('negative_keywords', ['client_id' => $client->id]);
    }

    public function test_delete_rejects_an_invalid_context(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($user)->delete("/clients/{$client->id}", ['context' => 'https://evil.example.com']);

        $response->assertSessionHasErrors('context');
        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }

    public function test_deleting_a_nonexistent_client_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/clients/99999', ['context' => 'keywords'])->assertNotFound();
    }
}
