<?php

namespace Tests\Feature;

use App\Enums\KeywordVaultType;
use App\Models\Niche;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class KeywordVaultImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_requires_authentication(): void
    {
        $niche = Niche::factory()->create();

        $this->get("/assets/keyword-vault/{$niche->industry->slug}/{$niche->id}/export")->assertRedirect('/login');
    }

    public function test_import_requires_authentication(): void
    {
        $niche = Niche::factory()->create();

        $this->post("/assets/keyword-vault/{$niche->industry->slug}/{$niche->id}/import")->assertRedirect('/login');
    }

    public function test_export_produces_a_spreadsheet_with_the_niches_keywords(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $niche->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'emergency plumber',
            'notes' => 'high intent',
        ]);
        $niche->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Negative,
            'keyword' => 'plumbing jobs',
        ]);

        $response = $this->actingAs($user)->get("/assets/keyword-vault/{$industry->slug}/{$niche->id}/export");

        $response->assertOk();

        $path = $response->baseResponse->getFile()->getRealPath();
        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray();

        $this->assertSame(['Keyword', 'Notes'], $rows[0]);
        $this->assertSame(['emergency plumber', 'high intent'], $rows[1]);
        $this->assertCount(2, $rows, 'Only the keyword-type entry should be exported, not the negative one.');
    }

    public function test_keywords_can_be_bulk_imported_from_a_spreadsheet(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $file = $this->makeSpreadsheetUpload([
            ['Keyword', 'Notes'],
            ['24 hour plumber', 'evening searches convert well'],
            ['drain cleaning service', ''],
        ]);

        $response = $this->actingAs($user)->post("/assets/keyword-vault/{$industry->slug}/{$niche->id}/import", [
            'file' => $file,
        ]);

        $response->assertRedirect("/assets/keyword-vault/{$industry->slug}/{$niche->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('keyword_vault_entries', [
            'niche_id' => $niche->id,
            'type' => 'keyword',
            'keyword' => '24 hour plumber',
            'notes' => 'evening searches convert well',
        ]);
        $this->assertDatabaseHas('keyword_vault_entries', [
            'niche_id' => $niche->id,
            'type' => 'keyword',
            'keyword' => 'drain cleaning service',
        ]);
    }

    public function test_importing_does_not_duplicate_an_existing_keyword(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $niche->keywordVaultEntries()->create([
            'type' => KeywordVaultType::Keyword,
            'keyword' => 'existing keyword',
        ]);

        $file = $this->makeSpreadsheetUpload([
            ['Keyword', 'Notes'],
            ['existing keyword', 'should not duplicate'],
        ]);

        $this->actingAs($user)->post("/assets/keyword-vault/{$industry->slug}/{$niche->id}/import", [
            'file' => $file,
        ]);

        $this->assertSame(
            1,
            $niche->keywordVaultEntries()->where('keyword', 'existing keyword')->count()
        );
    }

    public function test_importing_into_the_negative_vault_does_not_affect_the_keyword_vault(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $file = $this->makeSpreadsheetUpload([
            ['Keyword', 'Notes'],
            ['jobs', ''],
        ]);

        $this->actingAs($user)->post("/assets/negative-keyword-vault/{$industry->slug}/{$niche->id}/import", [
            'file' => $file,
        ]);

        $this->assertDatabaseHas('keyword_vault_entries', [
            'niche_id' => $niche->id,
            'type' => 'negative',
            'keyword' => 'jobs',
        ]);
        $this->assertDatabaseMissing('keyword_vault_entries', [
            'niche_id' => $niche->id,
            'type' => 'keyword',
            'keyword' => 'jobs',
        ]);
    }

    public function test_import_rejects_a_non_spreadsheet_file(): void
    {
        $user = User::factory()->create();
        $niche = Niche::factory()->create();
        $industry = $niche->industry;

        $response = $this->actingAs($user)->post("/assets/keyword-vault/{$industry->slug}/{$niche->id}/import", [
            'file' => UploadedFile::fake()->create('keywords.txt', 10, 'text/plain'),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('keyword_vault_entries', 0);
    }

    /**
     * Build a real .xlsx file (not an empty fake) so the import pipeline
     * has actual spreadsheet content to parse.
     *
     * @param  array<int, array<int, string>>  $rows
     */
    private function makeSpreadsheetUpload(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows);

        $path = tempnam(sys_get_temp_dir(), 'kwimport').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'keywords.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
