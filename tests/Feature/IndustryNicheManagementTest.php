<?php

namespace Tests\Feature;

use App\Enums\KeywordVaultType;
use App\Models\Industry;
use App\Models\Niche;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndustryNicheManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_industry_can_be_added(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/assets/industries', [
            'name' => 'Veterinary Clinics',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('industries', [
            'name' => 'Veterinary Clinics',
            'slug' => 'veterinary-clinics',
        ]);
    }

    public function test_industry_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        Industry::factory()->create(['name' => 'Legal Services']);

        $response = $this->actingAs($user)->post('/assets/industries', [
            'name' => 'Legal Services',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('industries', 1);
    }

    public function test_deleting_an_industry_cascades_to_its_niches_and_keywords(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create();
        $niche = Niche::factory()->for($industry)->create();
        $entry = $niche->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'some keyword',
        ]);

        $response = $this->actingAs($user)->delete("/assets/industries/{$industry->slug}");

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('industries', ['id' => $industry->id]);
        $this->assertDatabaseMissing('niches', ['id' => $niche->id]);
        $this->assertDatabaseMissing('keyword_vault_entries', ['id' => $entry->id]);
    }

    public function test_guests_cannot_add_or_delete_industries(): void
    {
        $industry = Industry::factory()->create();

        $this->post('/assets/industries', ['name' => 'Anything'])->assertRedirect('/login');
        $this->delete("/assets/industries/{$industry->slug}")->assertRedirect('/login');
    }

    public function test_a_niche_can_be_added_to_an_industry(): void
    {
        $user = User::factory()->create();
        $industry = Industry::factory()->create();

        $response = $this->actingAs($user)->post("/assets/industries/{$industry->slug}/niches", [
            'name' => 'Preschool',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('niches', [
            'industry_id' => $industry->id,
            'name' => 'Preschool',
            'slug' => 'preschool',
        ]);
    }

    public function test_niche_names_must_be_unique_within_an_industry_but_not_globally(): void
    {
        $user = User::factory()->create();
        $industryA = Industry::factory()->create();
        $industryB = Industry::factory()->create();
        Niche::factory()->for($industryA)->create(['name' => 'General']);

        // Same name, different industry — allowed.
        $response = $this->actingAs($user)->post("/assets/industries/{$industryB->slug}/niches", [
            'name' => 'General',
        ]);
        $response->assertSessionHasNoErrors();

        // Same name, same industry — rejected.
        $response = $this->actingAs($user)->post("/assets/industries/{$industryA->slug}/niches", [
            'name' => 'General',
        ]);
        $response->assertSessionHasErrors('name');
    }

    public function test_deleting_a_niche_cascades_to_its_keywords(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;
        $entry = $niche->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'some keyword',
        ]);

        $response = $this->actingAs($user)->delete("/assets/industries/{$industry->slug}/niches/{$niche->id}");

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('niches', ['id' => $niche->id]);
        $this->assertDatabaseMissing('keyword_vault_entries', ['id' => $entry->id]);
    }

    public function test_a_niche_cannot_be_deleted_through_a_mismatched_industry_url(): void
    {
        $user = User::factory()->create();
        $industryA = Industry::factory()->create();
        $niche = Niche::factory()->for(Industry::factory())->create();

        $response = $this->actingAs($user)->delete("/assets/industries/{$industryA->slug}/niches/{$niche->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('niches', ['id' => $niche->id]);
    }

    public function test_guests_cannot_add_or_delete_niches(): void
    {
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $this->post("/assets/industries/{$industry->slug}/niches", ['name' => 'Anything'])->assertRedirect('/login');
        $this->delete("/assets/industries/{$industry->slug}/niches/{$niche->id}")->assertRedirect('/login');
    }
}
