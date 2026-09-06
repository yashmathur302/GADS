<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeywordPlannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/planner')->assertRedirect('/login');
        $this->post('/planner')->assertRedirect('/login');
    }

    public function test_the_form_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/planner');

        $response->assertOk();
        $response->assertSeeText('Keyword Planner');
    }

    public function test_a_forecast_can_be_requested_for_a_keyword_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planner', [
            'keywords' => "emergency plumber\ndrain cleaning service",
            'max_cpc_bid' => 5,
        ]);

        $response->assertOk();
        $response->assertSeeText('emergency plumber');
        $response->assertSeeText('drain cleaning service');
        $response->assertSeeText('Total clicks');
    }

    public function test_keywords_are_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planner', [
            'keywords' => '',
            'max_cpc_bid' => 5,
        ]);

        $response->assertSessionHasErrors('keywords');
    }

    public function test_max_cpc_bid_must_be_a_positive_number(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planner', [
            'keywords' => 'plumber',
            'max_cpc_bid' => 0,
        ]);

        $response->assertSessionHasErrors('max_cpc_bid');
    }

    public function test_raising_the_bid_does_not_decrease_forecast_clicks(): void
    {
        $user = User::factory()->create();

        $lowBid = $this->actingAs($user)->post('/planner', [
            'keywords' => 'emergency plumber',
            'max_cpc_bid' => 0.10,
        ])->viewData('results')->first();

        $highBid = $this->actingAs($user)->post('/planner', [
            'keywords' => 'emergency plumber',
            'max_cpc_bid' => 50,
        ])->viewData('results')->first();

        $this->assertGreaterThanOrEqual($lowBid->clicks, $highBid->clicks);
        $this->assertGreaterThanOrEqual($lowBid->impressions, $highBid->impressions);
    }

    public function test_the_keyword_list_is_kept_in_the_form_after_a_forecast(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planner', [
            'keywords' => "emergency plumber\ndrain cleaning service",
            'max_cpc_bid' => 5,
        ]);

        // Checked as one adjacent multi-line block rather than via
        // assertSee, since both keywords also legitimately appear
        // separately in the results table below the form.
        $this->assertStringContainsString(
            "emergency plumber\ndrain cleaning service",
            $response->getContent(),
            'The submitted keyword list should still be in the textarea, not just the results table below it.'
        );
    }

    public function test_keyword_list_is_capped_at_twenty_and_deduplicated(): void
    {
        $user = User::factory()->create();

        $keywords = collect(range(1, 25))->map(fn ($i) => "keyword {$i}")->push('keyword 1')->implode("\n");

        $response = $this->actingAs($user)->post('/planner', [
            'keywords' => $keywords,
            'max_cpc_bid' => 1,
        ]);

        $response->assertOk();
        $this->assertCount(20, $response->viewData('results'));
    }
}
