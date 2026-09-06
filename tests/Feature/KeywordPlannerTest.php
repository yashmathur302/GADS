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

    public function test_max_cpc_bid_must_be_a_positive_number_when_given(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planner', [
            'keywords' => 'plumber',
            'max_cpc_bid' => 0,
        ]);

        $response->assertSessionHasErrors('max_cpc_bid');
    }

    public function test_max_cpc_bid_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planner', [
            'keywords' => 'emergency plumber',
        ]);

        $response->assertOk();
        $response->assertSessionHasNoErrors();
        $response->assertSeeText('emergency plumber');

        $forecast = $response->viewData('results')->first();
        $this->assertGreaterThan(0, $forecast->impressions);
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

    public function test_match_type_syntax_is_parsed_and_shown(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planner', [
            'keywords' => "plumber\n\"plumber\"\n[plumber]",
            'max_cpc_bid' => 5,
        ]);

        $results = $response->viewData('results');

        $this->assertSame(['Broad', 'Phrase', 'Exact'], $results->pluck('matchType.value')->all());
        // The bracket/quote match-type syntax should be stripped from the
        // parsed keyword itself, even though the textarea (a separate part
        // of the page) still shows what was typed, brackets and all.
        $this->assertSame(['plumber', 'plumber', 'plumber'], $results->pluck('keyword')->all());
    }

    public function test_forecast_period_scales_the_results(): void
    {
        $user = User::factory()->create();

        $sevenDays = $this->actingAs($user)->post('/planner', [
            'keywords' => 'emergency plumber',
            'max_cpc_bid' => 5,
            'forecast_days' => 7,
        ])->viewData('results')->first();

        $thirtyDays = $this->actingAs($user)->post('/planner', [
            'keywords' => 'emergency plumber',
            'max_cpc_bid' => 5,
            'forecast_days' => 30,
        ])->viewData('results')->first();

        $this->assertGreaterThan($sevenDays->impressions, $thirtyDays->impressions);
    }

    public function test_device_breakdown_percentages_add_up_to_100(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planner', [
            'keywords' => 'emergency plumber',
            'max_cpc_bid' => 5,
        ]);

        $breakdown = $response->viewData('deviceBreakdown');

        $this->assertEqualsWithDelta(
            100,
            $breakdown->desktopPercent + $breakdown->mobilePercent + $breakdown->tabletPercent,
            0.01
        );
    }

    public function test_bid_sweep_clicks_generally_rise_with_bid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planner', [
            'keywords' => 'emergency plumber',
            'max_cpc_bid' => 5,
        ]);

        $sweep = $response->viewData('bidSweep');

        $this->assertGreaterThanOrEqual($sweep->first()->clicks, $sweep->last()->clicks);
    }

    public function test_forecast_can_be_exported_to_csv(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planner/export', [
            'keywords' => 'emergency plumber',
            'max_cpc_bid' => 5,
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $rows = str_getcsv($response->streamedContent(), "\n");
        $this->assertStringContainsString('Keyword', $rows[0]);
        $this->assertStringContainsString('Match Type', $rows[0]);
    }

    public function test_export_requires_authentication(): void
    {
        $this->post('/planner/export')->assertRedirect('/login');
    }
}
