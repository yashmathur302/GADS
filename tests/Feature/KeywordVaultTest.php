<?php

namespace Tests\Feature;

use App\Enums\KeywordVaultType;
use App\Models\Industry;
use App\Models\Niche;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeywordVaultTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $this->get('/assets/keyword-vault')->assertRedirect('/login');
        $this->get("/assets/keyword-vault/{$industry->slug}")->assertRedirect('/login');
        $this->get("/assets/keyword-vault/{$industry->slug}/{$niche->id}")->assertRedirect('/login');
    }

    public function test_industries_index_shows_entry_counts_across_all_niches(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create(['name' => 'Legal Services']);
        $nicheA = Niche::factory()->for($industry)->create();
        $nicheB = Niche::factory()->for($industry)->create();

        $nicheA->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'personal injury lawyer',
        ]);
        $nicheB->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'divorce attorney',
        ]);
        $nicheA->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Negative,
            'keyword' => 'free legal advice',
        ]);

        $response = $this->actingAs($user)->get('/assets/keyword-vault');

        $response->assertOk();
        $response->assertSeeText('Legal Services');
        $response->assertSeeText('2');
    }

    public function test_industry_page_lists_its_niches_with_counts(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create();
        $niche = Niche::factory()->for($industry)->create(['name' => 'Dermatology Clinic']);

        $niche->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'skin specialist near me',
        ]);

        $response = $this->actingAs($user)->get("/assets/keyword-vault/{$industry->slug}");

        $response->assertOk();
        $response->assertSeeText('Dermatology Clinic');
        $response->assertSeeText('1');
    }

    public function test_keywords_can_be_added_to_a_niche(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $response = $this->actingAs($user)->post("/assets/keyword-vault/{$industry->slug}/{$niche->id}", [
            'keyword' => 'emergency plumber near me',
            'notes' => 'high intent',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/assets/keyword-vault/{$industry->slug}/{$niche->id}");

        $this->assertDatabaseHas('keyword_vault_entries', [
            'niche_id' => $niche->id,
            'type' => 'keyword',
            'keyword' => 'emergency plumber near me',
        ]);
    }

    public function test_negative_keywords_are_kept_separate_from_keywords(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $this->actingAs($user)->post("/assets/negative-keyword-vault/{$industry->slug}/{$niche->id}", [
            'keyword' => 'jobs',
        ]);

        $keywordVaultResponse = $this->actingAs($user)->get("/assets/keyword-vault/{$industry->slug}/{$niche->id}");
        $keywordVaultResponse->assertDontSeeText('jobs');

        $negativeVaultResponse = $this->actingAs($user)->get("/assets/negative-keyword-vault/{$industry->slug}/{$niche->id}");
        $negativeVaultResponse->assertSeeText('jobs');
    }

    public function test_adding_a_keyword_requires_a_value(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $response = $this->actingAs($user)->post("/assets/keyword-vault/{$industry->slug}/{$niche->id}", [
            'keyword' => '',
        ]);

        $response->assertSessionHasErrors('keyword');
    }

    public function test_a_keyword_can_be_removed(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;
        $entry = $niche->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'old keyword',
        ]);

        $response = $this->actingAs($user)->delete("/assets/keyword-vault/{$industry->slug}/{$niche->id}/{$entry->id}");

        $response->assertRedirect("/assets/keyword-vault/{$industry->slug}/{$niche->id}");
        $this->assertDatabaseMissing('keyword_vault_entries', ['id' => $entry->id]);
    }

    public function test_an_entry_cannot_be_deleted_through_a_mismatched_niche_url(): void
    {
        $user = User::factory()->create();
        $nicheA = Niche::factory()->create();
        $nicheB = Niche::factory()->create();

        $entry = $nicheA->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'belongs to niche A',
        ]);

        // Try to delete niche A's entry via niche B's URL.
        $response = $this->actingAs($user)->delete("/assets/keyword-vault/{$nicheB->industry->slug}/{$nicheB->id}/{$entry->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('keyword_vault_entries', ['id' => $entry->id]);
    }

    public function test_a_negative_entry_cannot_be_deleted_through_the_keyword_vault_url(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $entry = $niche->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Negative,
            'keyword' => 'a negative keyword',
        ]);

        // Same niche, but the wrong vault type in the URL.
        $response = $this->actingAs($user)->delete("/assets/keyword-vault/{$industry->slug}/{$niche->id}/{$entry->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('keyword_vault_entries', ['id' => $entry->id]);
    }

    public function test_a_niche_cannot_be_viewed_through_a_mismatched_industry_url(): void
    {
        $user = User::factory()->create();
        $industryA = Industry::factory()->create();
        $niche = Niche::factory()->for(Industry::factory())->create();

        $response = $this->actingAs($user)->get("/assets/keyword-vault/{$industryA->slug}/{$niche->id}");

        $response->assertNotFound();
    }

    public function test_an_unknown_industry_slug_404s(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/assets/keyword-vault/does-not-exist')->assertNotFound();
    }

    public function test_an_unknown_niche_id_404s(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create();

        $this->actingAs($user)->get("/assets/keyword-vault/{$industry->slug}/999999")->assertNotFound();
    }
}
