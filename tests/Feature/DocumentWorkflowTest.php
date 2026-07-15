<?php

namespace Tests\Feature;

use App\Models\CaseType;
use App\Models\ChecklistItem;
use App\Models\Client;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_case_generates_checklist_for_case_type(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        $caseType = CaseType::factory()->create(['name' => 'Consumidor']);

        $this->actingAs($user)->post(route('casos.store'), [
            'client_id' => $client->id,
            'case_type_id' => $caseType->id,
            'title' => 'Cobrança indevida',
            'status' => LegalCase::STATUS_NOVO_ATENDIMENTO,
        ])->assertRedirect();

        $case = $user->legalCases()->firstOrFail();
        $expectedItems = config('case_checklists.types.Consumidor');

        $this->assertCount(count($expectedItems), $case->checklistItems);
        $this->assertDatabaseHas('checklist_items', [
            'legal_case_id' => $case->id,
            'name' => 'Contrato ou comprovante da relação de consumo',
            'is_required' => true,
        ]);
    }

    public function test_default_checklist_can_be_generated_for_existing_case_without_duplicates(): void
    {
        [$user, $case] = $this->userAndCase('Família');

        $this->actingAs($user)
            ->post(route('casos.checklist.generate', $case))
            ->assertRedirect(route('casos.show', $case).'#documentos');

        $this->actingAs($user)
            ->post(route('casos.checklist.generate', $case))
            ->assertRedirect();

        $this->assertDatabaseCount('checklist_items', count(config('case_checklists.types.Família')));
    }

    public function test_user_can_add_and_complete_custom_checklist_item(): void
    {
        [$user, $case] = $this->userAndCase();

        $this->actingAs($user)->post(route('casos.checklist.store', $case), [
            'name' => '  Extrato bancário  ',
            'is_required' => '1',
        ])->assertRedirect();

        $item = $case->checklistItems()->firstOrFail();

        $this->actingAs($user)->patch(route('casos.checklist.update', [$case, $item]), [
            'is_completed' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('checklist_items', [
            'id' => $item->id,
            'name' => 'Extrato bancário',
            'is_required' => true,
            'is_completed' => true,
        ]);
        $this->assertNotNull($item->fresh()->completed_at);
    }

    public function test_user_cannot_modify_checklist_from_another_account(): void
    {
        $user = User::factory()->create();
        [, $otherCase] = $this->userAndCase();
        $item = ChecklistItem::create([
            'legal_case_id' => $otherCase->id,
            'name' => 'Documento protegido',
        ]);

        $this->actingAs($user)
            ->patch(route('casos.checklist.update', [$otherCase, $item]), ['is_completed' => '1'])
            ->assertNotFound();

        $this->assertFalse($item->fresh()->is_completed);
    }

    public function test_upload_marks_item_complete_and_download_is_authorized(): void
    {
        Storage::fake('local');
        [$user, $case] = $this->userAndCase();
        $item = ChecklistItem::create([
            'legal_case_id' => $case->id,
            'name' => 'Documento de identificação',
        ]);

        $this->actingAs($user)->post(route('casos.documents.store', $case), [
            'checklist_item_id' => $item->id,
            'document' => UploadedFile::fake()->create('identidade.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $document = $case->documents()->firstOrFail();

        Storage::disk('local')->assertExists($document->file_path);
        $this->assertTrue($item->fresh()->is_completed);
        $this->actingAs($user)
            ->get(route('casos.documents.download', [$case, $document]))
            ->assertOk()
            ->assertDownload('identidade.pdf');
    }

    public function test_document_from_another_account_cannot_be_downloaded(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        [, $otherCase] = $this->userAndCase();
        $document = $otherCase->documents()->create([
            'original_name' => 'segredo.pdf',
            'file_path' => 'case-documents/segredo.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'uploaded_at' => now(),
        ]);
        Storage::disk('local')->put($document->file_path, 'conteúdo protegido');

        $this->actingAs($user)
            ->get(route('casos.documents.download', [$otherCase, $document]))
            ->assertNotFound();
    }

    public function test_deleting_last_document_removes_file_and_reopens_pending_item(): void
    {
        Storage::fake('local');
        [$user, $case] = $this->userAndCase();
        $item = ChecklistItem::create([
            'legal_case_id' => $case->id,
            'name' => 'Comprovante',
            'is_completed' => true,
            'completed_at' => now(),
        ]);
        $document = $case->documents()->create([
            'checklist_item_id' => $item->id,
            'original_name' => 'comprovante.pdf',
            'file_path' => "case-documents/{$case->id}/comprovante.pdf",
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'uploaded_at' => now(),
        ]);
        Storage::disk('local')->put($document->file_path, 'arquivo');

        $this->actingAs($user)
            ->delete(route('casos.documents.destroy', [$case, $document]))
            ->assertRedirect();

        Storage::disk('local')->assertMissing($document->file_path);
        $this->assertDatabaseMissing('case_documents', ['id' => $document->id]);
        $this->assertFalse($item->fresh()->is_completed);
    }

    public function test_upload_rejects_unsupported_file_type(): void
    {
        Storage::fake('local');
        [$user, $case] = $this->userAndCase();

        $this->actingAs($user)->post(route('casos.documents.store', $case), [
            'document' => UploadedFile::fake()->create('programa.exe', 100, 'application/octet-stream'),
        ])->assertSessionHasErrors('document');

        $this->assertDatabaseCount('case_documents', 0);
    }

    public function test_document_whatsapp_message_contains_only_required_pending_items(): void
    {
        [$user, $case] = $this->userAndCase();
        $case->client->update([
            'name' => 'João Cliente',
            'phone' => '(15) 99999-0000',
        ]);
        ChecklistItem::create([
            'legal_case_id' => $case->id,
            'name' => 'CPF',
            'is_required' => true,
            'is_completed' => false,
        ]);
        ChecklistItem::create([
            'legal_case_id' => $case->id,
            'name' => 'Comprovante já recebido',
            'is_required' => true,
            'is_completed' => true,
        ]);
        ChecklistItem::create([
            'legal_case_id' => $case->id,
            'name' => 'Documento opcional',
            'is_required' => false,
            'is_completed' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('casos.documents.whatsapp', $case));

        $location = (string) $response->headers->get('Location');

        $response->assertRedirect();
        $this->assertStringContainsString(rawurlencode('CPF'), $location);
        $this->assertStringNotContainsString(rawurlencode('Comprovante já recebido'), $location);
        $this->assertStringNotContainsString(rawurlencode('Documento opcional'), $location);
    }

    public function test_case_page_displays_document_progress(): void
    {
        [$user, $case] = $this->userAndCase();
        ChecklistItem::create([
            'legal_case_id' => $case->id,
            'name' => 'CPF',
            'is_required' => true,
            'is_completed' => true,
        ]);
        ChecklistItem::create([
            'legal_case_id' => $case->id,
            'name' => 'Comprovante de residência',
            'is_required' => true,
            'is_completed' => false,
        ]);

        $this->actingAs($user)
            ->get(route('casos.show', $case))
            ->assertOk()
            ->assertSee('1 de 2 documentos obrigatórios concluídos')
            ->assertSee('50%');
    }

    public function test_case_with_checklist_and_without_files_can_be_deleted(): void
    {
        [$user, $case] = $this->userAndCase();
        ChecklistItem::create([
            'legal_case_id' => $case->id,
            'name' => 'Pendência sem arquivo',
        ]);

        $this->actingAs($user)
            ->delete(route('casos.destroy', $case))
            ->assertRedirect(route('casos.index'));

        $this->assertDatabaseMissing('legal_cases', ['id' => $case->id]);
        $this->assertDatabaseCount('checklist_items', 0);
    }

    public function test_case_with_stored_document_cannot_be_deleted(): void
    {
        [$user, $case] = $this->userAndCase();
        $case->documents()->create([
            'original_name' => 'documento.pdf',
            'file_path' => "case-documents/{$case->id}/documento.pdf",
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'uploaded_at' => now(),
        ]);

        $this->actingAs($user)
            ->from(route('casos.show', $case))
            ->delete(route('casos.destroy', $case))
            ->assertRedirect(route('casos.show', $case))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('legal_cases', ['id' => $case->id]);
    }

    /**
     * @return array{User, LegalCase}
     */
    private function userAndCase(string $caseTypeName = 'Consumidor'): array
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        $caseType = CaseType::factory()->create(['name' => $caseTypeName]);
        $case = LegalCase::factory()->for($user)->for($client)->for($caseType)->create();

        return [$user, $case];
    }
}
