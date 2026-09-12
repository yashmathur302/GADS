<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LocationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/locations')->assertRedirect('/login');
    }

    public function test_a_client_can_be_created_from_the_location_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/clients', [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
            'context' => 'location',
        ]);

        $response->assertRedirect(route('locations.index'));
        $this->assertDatabaseHas('clients', [
            'name' => 'Acme Plumbing',
            'type' => 'location',
        ]);
    }

    public function test_a_client_created_from_location_does_not_appear_on_the_keywords_page(): void
    {
        $user = User::factory()->create();
        Client::factory()->create(['name' => 'Acme Plumbing', 'type' => Client::TYPE_LOCATION]);

        $response = $this->actingAs($user)->get('/keywords');

        $response->assertDontSeeText('Acme Plumbing');
    }

    public function test_the_index_lists_clients_with_their_location_count(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['name' => 'Acme Plumbing', 'type' => Client::TYPE_LOCATION]);
        $client->locations()->create(['location' => 'New York, NY']);
        $client->locations()->create(['location' => 'Los Angeles, CA']);

        $response = $this->actingAs($user)->get('/locations');

        $response->assertSeeText('Acme Plumbing');
        $response->assertSeeText('2');
    }

    public function test_the_client_detail_page_lists_its_locations(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_LOCATION]);
        $client->locations()->create(['location' => 'New York, NY']);

        $response = $this->actingAs($user)->get("/locations/{$client->id}");

        $response->assertOk();
        $response->assertSeeText('New York, NY');
    }

    public function test_a_keywords_type_client_is_not_reachable_via_the_location_routes(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_KEYWORDS]);

        $this->actingAs($user)->get("/locations/{$client->id}")->assertNotFound();
    }

    public function test_importing_a_csv_adds_new_locations(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_LOCATION]);

        $csv = "Location\nNew York, NY\nLos Angeles, CA\n";
        $file = UploadedFile::fake()->createWithContent('locations.csv', $csv);

        $response = $this->actingAs($user)->post("/locations/{$client->id}/import", ['file' => $file]);

        $response->assertRedirect(route('locations.show', $client));
        $this->assertDatabaseHas('locations', ['location' => 'New York, NY']);
        $this->assertDatabaseHas('locations', ['location' => 'Los Angeles, CA']);
        $this->assertSame(2, $client->locations()->count());
    }

    public function test_importing_duplicate_locations_skips_them_and_reports_the_count(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_LOCATION]);
        $client->locations()->create(['location' => 'New York, NY']);

        $csv = "New York, NY\nChicago, IL\n";
        $file = UploadedFile::fake()->createWithContent('locations.csv', $csv);

        $response = $this->actingAs($user)->post("/locations/{$client->id}/import", ['file' => $file]);

        $response->assertSessionHas('status', function ($message) {
            return str_contains($message, '1 location imported') && str_contains($message, '1 entry skipped as duplicate');
        });
        $this->assertSame(2, $client->locations()->count());
    }

    public function test_import_rejects_non_csv_files(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_LOCATION]);

        $file = UploadedFile::fake()->create('locations.pdf', 10, 'application/pdf');

        $response = $this->actingAs($user)->post("/locations/{$client->id}/import", ['file' => $file]);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, $client->locations()->count());
    }

    public function test_locations_can_be_exported_to_csv(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_LOCATION]);
        $client->locations()->create(['location' => 'New York, NY']);

        $response = $this->actingAs($user)->get("/locations/{$client->id}/export");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $rows = array_map('str_getcsv', explode("\n", trim($response->streamedContent())));
        $this->assertSame(['Location'], $rows[0]);
        $this->assertSame(['New York, NY'], $rows[1]);
    }
}
