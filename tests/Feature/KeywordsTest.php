<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class KeywordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/keywords')->assertRedirect('/login');
        $this->post('/clients', ['context' => 'keywords'])->assertRedirect('/login');
    }

    public function test_a_client_can_be_created_from_the_keywords_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/clients', [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
            'context' => 'keywords',
        ]);

        $response->assertRedirect(route('keywords.index'));
        $this->assertDatabaseHas('clients', [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
        ]);
    }

    public function test_client_creation_requires_name_and_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/clients', ['context' => 'keywords']);

        $response->assertSessionHasErrors(['name', 'industry_category']);
    }

    public function test_client_creation_rejects_an_invalid_context(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/clients', [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
            'context' => 'https://evil.example.com',
        ]);

        $response->assertSessionHasErrors('context');
    }

    public function test_a_client_created_from_negative_keywords_does_not_appear_on_the_keywords_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/clients', [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
            'context' => 'negative-keywords',
        ]);

        $response = $this->actingAs($user)->get('/keywords');

        $this->assertFalse($response->viewData('clients')->contains('name', 'Acme Plumbing'));
    }

    public function test_a_created_client_is_stored_with_the_keywords_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/clients', [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
            'context' => 'keywords',
        ]);

        $this->assertDatabaseHas('clients', ['name' => 'Acme Plumbing', 'type' => 'keywords']);
    }

    public function test_the_keywords_index_lists_clients_with_their_keyword_count(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['name' => 'Acme Plumbing']);
        $client->keywords()->createMany([
            ['keyword' => 'emergency plumber', 'match_type' => 'Broad'],
            ['keyword' => 'drain cleaning', 'match_type' => 'Exact'],
        ]);

        $response = $this->actingAs($user)->get('/keywords');

        $response->assertSeeText('Acme Plumbing');
        $response->assertSeeText('2');
    }

    public function test_the_client_detail_page_lists_its_keywords(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->keywords()->create(['keyword' => 'emergency plumber', 'match_type' => 'Exact']);

        $response = $this->actingAs($user)->get("/keywords/{$client->id}");

        $response->assertOk();
        $response->assertSeeText('emergency plumber');
        $response->assertSeeText('Exact');
    }

    public function test_a_nonexistent_client_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/keywords/99999')->assertNotFound();
    }

    public function test_importing_a_csv_adds_new_keywords(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        // A literal quoted phrase in a real CSV file must double-escape the
        // inner quotes (RFC 4180) — a bare "drain cleaning" would just be
        // CSV's own field-quoting syntax, stripped before it ever reaches
        // MatchType::parse().
        $csv = "keyword\nemergency plumber\n\"\"\"drain cleaning\"\"\"\n[24 hour plumber]\n";
        $file = UploadedFile::fake()->createWithContent('keywords.csv', $csv);

        $response = $this->actingAs($user)->post("/keywords/{$client->id}/import", ['file' => $file]);

        $response->assertRedirect(route('keywords.show', $client));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('keywords', ['keyword' => 'emergency plumber', 'match_type' => 'Broad']);
        $this->assertDatabaseHas('keywords', ['keyword' => 'drain cleaning', 'match_type' => 'Phrase']);
        $this->assertDatabaseHas('keywords', ['keyword' => '24 hour plumber', 'match_type' => 'Exact']);
        $this->assertSame(3, $client->keywords()->count());
    }

    public function test_importing_duplicate_keywords_skips_them_and_reports_the_count(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->keywords()->create(['keyword' => 'emergency plumber', 'match_type' => 'Broad']);

        $csv = "emergency plumber\nnew keyword\n";
        $file = UploadedFile::fake()->createWithContent('keywords.csv', $csv);

        $response = $this->actingAs($user)->post("/keywords/{$client->id}/import", ['file' => $file]);

        $response->assertSessionHas('status', function ($message) {
            return str_contains($message, '1 keyword imported') && str_contains($message, '1 entry skipped as duplicate');
        });

        $this->assertSame(2, $client->keywords()->count());
    }

    public function test_importing_the_same_file_twice_skips_everything_the_second_time(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $csv = "keyword one\nkeyword two\n";

        $this->actingAs($user)->post("/keywords/{$client->id}/import", [
            'file' => UploadedFile::fake()->createWithContent('keywords.csv', $csv),
        ]);

        $this->actingAs($user)->post("/keywords/{$client->id}/import", [
            'file' => UploadedFile::fake()->createWithContent('keywords.csv', $csv),
        ]);

        $this->assertSame(2, $client->keywords()->count());
    }

    public function test_import_rejects_non_csv_files(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $file = UploadedFile::fake()->create('keywords.pdf', 10, 'application/pdf');

        $response = $this->actingAs($user)->post("/keywords/{$client->id}/import", ['file' => $file]);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, $client->keywords()->count());
    }

    public function test_keywords_can_be_exported_to_csv(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->keywords()->create(['keyword' => 'emergency plumber', 'match_type' => 'Exact']);

        $response = $this->actingAs($user)->get("/keywords/{$client->id}/export");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $rows = array_map('str_getcsv', explode("\n", trim($response->streamedContent())));
        $this->assertSame(['Keyword', 'Match Type'], $rows[0]);
        $this->assertSame(['emergency plumber', 'Exact'], $rows[1]);
    }
}
