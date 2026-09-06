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

        foreach (['Avg. monthly searches', 'Competition', 'Competition index', 'Low range CPC', 'High range CPC'] as $column) {
            $response->assertSeeText($column);
        }
    }
}
