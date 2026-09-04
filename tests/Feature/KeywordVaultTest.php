<?php

namespace Tests\Feature;

use App\Enums\KeywordVaultType;
use App\Models\Industry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeywordVaultTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $industry = Industry::factory()->create();

        $this->get('/assets/keyword-vault')->assertRedirect('/login');
        $this->get("/assets/keyword-vault/{$industry->slug}")->assertRedirect('/login');
    }

    public function test_industries_index_shows_entry_counts_per_vault_type(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create(['name' => 'Legal Services']);

        $industry->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'personal injury lawyer',
        ]);
        $industry->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Negative,
            'keyword' => 'free legal advice',
        ]);

        $response = $this->actingAs($user)->get('/assets/keyword-vault');

        $response->assertOk();
        $response->assertSeeText('Legal Services');
        $response->assertSeeText('1');
    }

    public function test_keywords_can_be_added_to_an_industry(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create();

        $response = $this->actingAs($user)->post("/assets/keyword-vault/{$industry->slug}", [
            'keyword' => 'emergency plumber near me',
            'notes' => 'high intent',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/assets/keyword-vault/{$industry->slug}");

        $this->assertDatabaseHas('keyword_vault_entries', [
            'industry_id' => $industry->id,
            'type' => 'keyword',
            'keyword' => 'emergency plumber near me',
        ]);
    }

    public function test_negative_keywords_are_kept_separate_from_keywords(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create();

        $this->actingAs($user)->post("/assets/negative-keyword-vault/{$industry->slug}", [
            'keyword' => 'jobs',
        ]);

        $keywordVaultResponse = $this->actingAs($user)->get("/assets/keyword-vault/{$industry->slug}");
        $keywordVaultResponse->assertDontSeeText('jobs');

        $negativeVaultResponse = $this->actingAs($user)->get("/assets/negative-keyword-vault/{$industry->slug}");
        $negativeVaultResponse->assertSeeText('jobs');
    }

    public function test_adding_a_keyword_requires_a_value(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create();

        $response = $this->actingAs($user)->post("/assets/keyword-vault/{$industry->slug}", [
            'keyword' => '',
        ]);

        $response->assertSessionHasErrors('keyword');
    }

    public function test_a_keyword_can_be_removed(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create();
        $entry = $industry->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'old keyword',
        ]);

        $response = $this->actingAs($user)->delete("/assets/keyword-vault/{$industry->slug}/{$entry->id}");

        $response->assertRedirect("/assets/keyword-vault/{$industry->slug}");
        $this->assertDatabaseMissing('keyword_vault_entries', ['id' => $entry->id]);
    }

    public function test_an_entry_cannot_be_deleted_through_a_mismatched_industry_url(): void
    {
        $user = User::factory()->create();
        $industryA = Industry::factory()->create();
        $industryB = Industry::factory()->create();

        $entry = $industryA->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'belongs to industry A',
        ]);

        // Try to delete industry A's entry via industry B's URL.
        $response = $this->actingAs($user)->delete("/assets/keyword-vault/{$industryB->slug}/{$entry->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('keyword_vault_entries', ['id' => $entry->id]);
    }

    public function test_a_negative_entry_cannot_be_deleted_through_the_keyword_vault_url(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create();

        $entry = $industry->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Negative,
            'keyword' => 'a negative keyword',
        ]);

        // Same industry, but the wrong vault type in the URL.
        $response = $this->actingAs($user)->delete("/assets/keyword-vault/{$industry->slug}/{$entry->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('keyword_vault_entries', ['id' => $entry->id]);
    }

    public function test_an_unknown_industry_slug_404s(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/assets/keyword-vault/does-not-exist')->assertNotFound();
    }
}
