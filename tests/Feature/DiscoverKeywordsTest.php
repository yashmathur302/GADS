<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoverKeywordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/discover')->assertRedirect('/login');
        $this->post('/discover')->assertRedirect('/login');
    }

    public function test_the_form_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/discover');

        $response->assertOk();
        $response->assertSeeText('Discover New Keywords');
    }

    public function test_searching_by_seed_keywords_returns_keyword_ideas(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/discover', [
            'seed_keywords' => 'emergency plumber',
        ]);

        $response->assertOk();
        $response->assertSeeText('emergency plumber');
        $response->assertSeeText('Avg. monthly searches');
    }

    public function test_searching_by_website_url_returns_keyword_ideas(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/discover', [
            'website_url' => 'https://example.com/emergency-plumber-services',
        ]);

        $response->assertOk();
        $response->assertSeeText('emergency plumber services');
    }

    public function test_at_least_one_of_seed_keywords_or_website_url_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/discover', []);

        $response->assertSessionHasErrors(['seed_keywords', 'website_url']);
    }

    public function test_results_are_deterministic_for_the_same_keyword(): void
    {
        $user = User::factory()->create();

        $first = $this->actingAs($user)->post('/discover', ['seed_keywords' => 'drain cleaning']);
        $second = $this->actingAs($user)->post('/discover', ['seed_keywords' => 'drain cleaning']);

        $first->assertOk();
        $second->assertOk();
        $this->assertSame($first->getContent(), $second->getContent());
    }

    public function test_results_include_the_requested_metric_columns(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/discover', [
            'seed_keywords' => 'drain cleaning',
        ]);

        foreach (['Avg. monthly searches', '3 mo. change', 'YoY change', 'Competition', 'Competition index', 'Low range CPC', 'High range CPC'] as $column) {
            $response->assertSeeText($column);
        }
    }

    public function test_changing_the_location_changes_the_results(): void
    {
        $user = User::factory()->create();

        $us = $this->actingAs($user)->post('/discover', [
            'seed_keywords' => 'drain cleaning',
            'location' => 'US',
        ])->viewData('results')->first();

        $uk = $this->actingAs($user)->post('/discover', [
            'seed_keywords' => 'drain cleaning',
            'location' => 'GB',
        ])->viewData('results')->first();

        $this->assertNotSame($us->avgMonthlySearches, $uk->avgMonthlySearches);
    }

    public function test_including_search_partners_increases_search_volume(): void
    {
        $user = User::factory()->create();

        $withoutPartners = $this->actingAs($user)->post('/discover', [
            'seed_keywords' => 'drain cleaning',
        ])->viewData('results')->first();

        $withPartners = $this->actingAs($user)->post('/discover', [
            'seed_keywords' => 'drain cleaning',
            'include_search_partners' => '1',
        ])->viewData('results')->first();

        $this->assertGreaterThan($withoutPartners->avgMonthlySearches, $withPartners->avgMonthlySearches);
    }

    public function test_results_can_be_sorted_by_column(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/discover', [
            'seed_keywords' => 'drain cleaning',
            'sort_spec' => 'keyword:asc',
        ]);

        $keywords = $response->viewData('results')->pluck('keyword')->values()->all();
        $sorted = $keywords;
        sort($sorted);

        $this->assertSame($sorted, $keywords);
    }

    public function test_results_can_be_filtered_by_competition(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/discover', [
            'seed_keywords' => 'drain cleaning',
            'competition' => ['Low'],
        ]);

        $competitions = $response->viewData('results')->pluck('competition')->unique()->values()->all();

        $this->assertSame(['Low'], $competitions);
    }

    public function test_results_can_be_filtered_by_minimum_searches(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/discover', [
            'seed_keywords' => 'drain cleaning',
            'min_searches' => 5000,
        ]);

        $tooLow = $response->viewData('results')->first(fn ($idea) => $idea->avgMonthlySearches < 5000);

        $this->assertNull($tooLow);
    }

    public function test_results_can_be_exported_to_csv(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/discover/export', [
            'seed_keywords' => 'drain cleaning',
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $rows = str_getcsv($response->streamedContent(), "\n");
        $this->assertStringContainsString('Keyword', $rows[0]);
        $this->assertGreaterThan(1, count($rows));
    }

    public function test_export_requires_authentication(): void
    {
        $this->post('/discover/export')->assertRedirect('/login');
    }
}
