<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BlogKeywordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/blog-keywords')->assertRedirect('/login');
    }

    public function test_a_client_can_be_created_from_the_blog_keywords_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/clients', [
            'name' => 'Acme Plumbing',
            'industry_category' => 'Home Services',
            'context' => 'blog-keywords',
        ]);

        $response->assertRedirect(route('blog-keywords.index'));
        $this->assertDatabaseHas('clients', [
            'name' => 'Acme Plumbing',
            'type' => 'blog-keywords',
        ]);
    }

    public function test_a_client_created_from_blog_keywords_does_not_appear_on_the_keywords_page(): void
    {
        $user = User::factory()->create();
        Client::factory()->create(['name' => 'Acme Plumbing', 'type' => Client::TYPE_BLOG_KEYWORDS]);

        $response = $this->actingAs($user)->get('/keywords');

        $response->assertDontSeeText('Acme Plumbing');
    }

    public function test_the_index_lists_clients_with_their_blog_keyword_count(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['name' => 'Acme Plumbing', 'type' => Client::TYPE_BLOG_KEYWORDS]);
        $client->blogKeywords()->create(['keyword' => 'how to unclog a drain']);
        $client->blogKeywords()->create(['keyword' => 'emergency plumber tips']);

        $response = $this->actingAs($user)->get('/blog-keywords');

        $response->assertSeeText('Acme Plumbing');
        $response->assertSeeText('2');
    }

    public function test_the_client_detail_page_lists_its_blog_keywords(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_BLOG_KEYWORDS]);
        $client->blogKeywords()->create(['keyword' => 'how to unclog a drain']);

        $response = $this->actingAs($user)->get("/blog-keywords/{$client->id}");

        $response->assertOk();
        $response->assertSeeText('how to unclog a drain');
    }

    public function test_a_keywords_type_client_is_not_reachable_via_the_blog_keywords_routes(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_KEYWORDS]);

        $this->actingAs($user)->get("/blog-keywords/{$client->id}")->assertNotFound();
    }

    public function test_importing_a_csv_adds_new_blog_keywords(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_BLOG_KEYWORDS]);

        $csv = "Keyword\nhow to unclog a drain\nemergency plumber tips\n";
        $file = UploadedFile::fake()->createWithContent('blog-keywords.csv', $csv);

        $response = $this->actingAs($user)->post("/blog-keywords/{$client->id}/import", ['file' => $file]);

        $response->assertRedirect(route('blog-keywords.show', $client));
        $this->assertDatabaseHas('blog_keywords', ['keyword' => 'how to unclog a drain']);
        $this->assertDatabaseHas('blog_keywords', ['keyword' => 'emergency plumber tips']);
        $this->assertSame(2, $client->blogKeywords()->count());
    }

    public function test_importing_duplicate_blog_keywords_skips_them_and_reports_the_count(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_BLOG_KEYWORDS]);
        $client->blogKeywords()->create(['keyword' => 'how to unclog a drain']);

        $csv = "how to unclog a drain\nemergency plumber tips\n";
        $file = UploadedFile::fake()->createWithContent('blog-keywords.csv', $csv);

        $response = $this->actingAs($user)->post("/blog-keywords/{$client->id}/import", ['file' => $file]);

        $response->assertSessionHas('status', function ($message) {
            return str_contains($message, '1 blog keyword imported') && str_contains($message, '1 entry skipped as duplicate');
        });
        $this->assertSame(2, $client->blogKeywords()->count());
    }

    public function test_import_rejects_non_csv_files(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_BLOG_KEYWORDS]);

        $file = UploadedFile::fake()->create('blog-keywords.pdf', 10, 'application/pdf');

        $response = $this->actingAs($user)->post("/blog-keywords/{$client->id}/import", ['file' => $file]);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, $client->blogKeywords()->count());
    }

    public function test_blog_keywords_can_be_exported_to_csv(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_BLOG_KEYWORDS]);
        $client->blogKeywords()->create(['keyword' => 'how to unclog a drain']);

        $response = $this->actingAs($user)->get("/blog-keywords/{$client->id}/export");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $rows = array_map('str_getcsv', explode("\n", trim($response->streamedContent())));
        $this->assertSame(['Keyword'], $rows[0]);
        $this->assertSame(['how to unclog a drain'], $rows[1]);
    }

    public function test_a_single_blog_keyword_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_BLOG_KEYWORDS]);
        $blogKeyword = $client->blogKeywords()->create(['keyword' => 'how to unclog a drain']);

        $response = $this->actingAs($user)->delete("/blog-keywords/{$client->id}/{$blogKeyword->id}");

        $response->assertRedirect(route('blog-keywords.show', $client));
        $this->assertDatabaseMissing('blog_keywords', ['id' => $blogKeyword->id]);
    }

    public function test_deleting_a_blog_keyword_belonging_to_a_different_client_returns_404(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['type' => Client::TYPE_BLOG_KEYWORDS]);
        $otherClient = Client::factory()->create(['type' => Client::TYPE_BLOG_KEYWORDS]);
        $blogKeyword = $otherClient->blogKeywords()->create(['keyword' => 'how to unclog a drain']);

        $this->actingAs($user)->delete("/blog-keywords/{$client->id}/{$blogKeyword->id}")->assertNotFound();
        $this->assertDatabaseHas('blog_keywords', ['id' => $blogKeyword->id]);
    }
}
