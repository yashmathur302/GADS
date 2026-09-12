<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class NegativeKeywordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/negative-keywords')->assertRedirect('/login');
    }

    public function test_a_client_created_from_the_negative_keywords_page_redirects_back_there(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/clients', [
            'name' => 'Bright Smiles Dental',
            'industry_category' => 'Healthcare',
            'context' => 'negative-keywords',
        ]);

        $response->assertRedirect(route('negative-keywords.index'));
    }

    public function test_the_index_lists_clients_with_their_negative_keyword_count(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['name' => 'Acme Plumbing']);
        $client->negativeKeywords()->create(['keyword' => 'free', 'match_type' => 'Broad']);

        $response = $this->actingAs($user)->get('/negative-keywords');

        $response->assertSeeText('Acme Plumbing');
        $response->assertSeeText('1');
    }

    public function test_importing_a_csv_supports_match_types(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $csv = "\"\"\"cheap\"\"\"\n[free]\nplain broad term\n";
        $file = UploadedFile::fake()->createWithContent('negatives.csv', $csv);

        $response = $this->actingAs($user)->post("/negative-keywords/{$client->id}/import", ['file' => $file]);

        $response->assertRedirect(route('negative-keywords.show', $client));
        $this->assertDatabaseHas('negative_keywords', ['keyword' => 'cheap', 'match_type' => 'Phrase']);
        $this->assertDatabaseHas('negative_keywords', ['keyword' => 'free', 'match_type' => 'Exact']);
        $this->assertDatabaseHas('negative_keywords', ['keyword' => 'plain broad term', 'match_type' => 'Broad']);
    }

    public function test_a_keyword_and_a_negative_keyword_with_the_same_text_do_not_collide(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->keywords()->create(['keyword' => 'plumber', 'match_type' => 'Broad']);

        $file = UploadedFile::fake()->createWithContent('negatives.csv', "plumber\n");
        $this->actingAs($user)->post("/negative-keywords/{$client->id}/import", ['file' => $file]);

        $this->assertSame(1, $client->keywords()->count());
        $this->assertSame(1, $client->negativeKeywords()->count());
    }

    public function test_negative_keywords_can_be_exported_to_csv(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $client->negativeKeywords()->create(['keyword' => 'free', 'match_type' => 'Broad']);

        $response = $this->actingAs($user)->get("/negative-keywords/{$client->id}/export");

        $response->assertOk();
        $rows = array_map('str_getcsv', explode("\n", trim($response->streamedContent())));
        $this->assertSame(['Keyword', 'Match Type'], $rows[0]);
        $this->assertSame(['free', 'Broad'], $rows[1]);
    }
}
