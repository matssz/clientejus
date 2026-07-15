<?php

namespace Tests\Feature;

use App\Models\CaseType;
use App\Models\Client;
use App\Models\Contract;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_contracts(): void
    {
        $this->get(route('contratos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_cases_is_sent_to_case_registration(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('contratos.create'))
            ->assertRedirect(route('casos.create'))
            ->assertSessionHas('warning');
    }

    public function test_user_can_create_contract_with_private_original_document(): void
    {
        Storage::fake('local');
        [$user, $case] = $this->userAndCase();

        $this->actingAs($user)->post(route('contratos.store'), [
            'legal_case_id' => $case->id,
            'title' => 'Contrato de prestação de serviços',
            'signed_at' => '2026-07-01',
            'expires_at' => '2027-07-01',
            'status' => Contract::STATUS_ACTIVE,
            'original_document' => UploadedFile::fake()->create('contrato.pdf', 200, 'application/pdf'),
        ])->assertRedirect();

        $contract = $case->contracts()->firstOrFail();

        $this->assertSame('Contrato de prestação de serviços', $contract->title);
        $this->assertSame('contrato.pdf', $contract->original_document_name);
        Storage::disk('local')->assertExists($contract->original_document_path);

        $this->actingAs($user)
            ->get(route('contratos.download', $contract))
            ->assertOk()
            ->assertDownload('contrato.pdf');
    }

    public function test_user_cannot_create_contract_for_another_users_case(): void
    {
        $user = User::factory()->create();
        [, $otherCase] = $this->userAndCase();

        $this->actingAs($user)->post(route('contratos.store'), [
            'legal_case_id' => $otherCase->id,
            'title' => 'Contrato indevido',
            'signed_at' => '2026-07-01',
            'status' => Contract::STATUS_ACTIVE,
        ])->assertSessionHasErrors('legal_case_id');

        $this->assertDatabaseCount('contracts', 0);
    }

    public function test_user_sees_only_their_own_contracts(): void
    {
        [$user, $case] = $this->userAndCase();
        [, $otherCase] = $this->userAndCase();
        Contract::factory()->for($case, 'legalCase')->create(['title' => 'Contrato visível']);
        Contract::factory()->for($otherCase, 'legalCase')->create(['title' => 'Contrato protegido']);

        $this->actingAs($user)
            ->get(route('contratos.index'))
            ->assertOk()
            ->assertSee('Contrato visível')
            ->assertDontSee('Contrato protegido');
    }

    public function test_user_cannot_view_update_or_download_another_users_contract(): void
    {
        Storage::fake('local');
        [$user, $ownCase] = $this->userAndCase();
        [, $otherCase] = $this->userAndCase();
        $contract = Contract::factory()->for($otherCase, 'legalCase')->create([
            'original_document_path' => 'contracts/protected/contract.pdf',
            'original_document_name' => 'contract.pdf',
        ]);
        Storage::disk('local')->put($contract->original_document_path, 'protected');

        $this->actingAs($user)
            ->get(route('contratos.show', $contract))
            ->assertNotFound();

        $this->actingAs($user)
            ->put(route('contratos.update', $contract), [
                'legal_case_id' => $ownCase->id,
                'title' => 'Tentativa de alteração',
                'signed_at' => '2026-07-01',
                'status' => Contract::STATUS_ACTIVE,
            ])->assertNotFound();

        $this->actingAs($user)
            ->get(route('contratos.download', $contract))
            ->assertNotFound();

        $this->assertNotSame('Tentativa de alteração', $contract->fresh()->title);
    }

    public function test_replacing_original_document_deletes_previous_file(): void
    {
        Storage::fake('local');
        [$user, $case] = $this->userAndCase();
        $contract = Contract::factory()->for($case, 'legalCase')->create([
            'original_document_path' => "contracts/{$case->id}/old.pdf",
            'original_document_name' => 'old.pdf',
        ]);
        Storage::disk('local')->put($contract->original_document_path, 'old');

        $this->actingAs($user)->put(route('contratos.update', $contract), [
            'legal_case_id' => $case->id,
            'title' => $contract->title,
            'signed_at' => $contract->signed_at->toDateString(),
            'expires_at' => $contract->expires_at->toDateString(),
            'status' => Contract::STATUS_ACTIVE,
            'original_document' => UploadedFile::fake()->create('new.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('contratos.show', $contract));

        $contract->refresh();

        Storage::disk('local')->assertMissing("contracts/{$case->id}/old.pdf");
        Storage::disk('local')->assertExists($contract->original_document_path);
        $this->assertSame('new.pdf', $contract->original_document_name);
    }

    public function test_deleting_contract_removes_original_document(): void
    {
        Storage::fake('local');
        [$user, $case] = $this->userAndCase();
        $contract = Contract::factory()->for($case, 'legalCase')->create([
            'original_document_path' => 'contracts/1/original.pdf',
        ]);
        Storage::disk('local')->put($contract->original_document_path, 'content');

        $this->actingAs($user)
            ->delete(route('contratos.destroy', $contract))
            ->assertRedirect(route('contratos.index'));

        Storage::disk('local')->assertMissing('contracts/1/original.pdf');
        $this->assertDatabaseMissing('contracts', ['id' => $contract->id]);
    }

    public function test_case_with_contract_cannot_be_deleted(): void
    {
        [$user, $case] = $this->userAndCase();
        Contract::factory()->for($case, 'legalCase')->create();

        $this->actingAs($user)
            ->from(route('casos.show', $case))
            ->delete(route('casos.destroy', $case))
            ->assertRedirect(route('casos.show', $case))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('legal_cases', ['id' => $case->id]);
    }

    public function test_contract_page_warns_about_upcoming_expiration(): void
    {
        [$user, $case] = $this->userAndCase();
        $contract = Contract::factory()->for($case, 'legalCase')->create([
            'expires_at' => now()->addDays(10)->toDateString(),
            'status' => Contract::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('contratos.show', $contract))
            ->assertOk()
            ->assertSee('Vencimento previsto');
    }

    public function test_contract_rejects_unsupported_document_type(): void
    {
        Storage::fake('local');
        [$user, $case] = $this->userAndCase();

        $this->actingAs($user)->post(route('contratos.store'), [
            'legal_case_id' => $case->id,
            'title' => 'Contrato inválido',
            'signed_at' => '2026-07-01',
            'status' => Contract::STATUS_ACTIVE,
            'original_document' => UploadedFile::fake()->create('script.exe', 50, 'application/octet-stream'),
        ])->assertSessionHasErrors('original_document');

        $this->assertDatabaseCount('contracts', 0);
    }

    /**
     * @return array{User, LegalCase}
     */
    private function userAndCase(): array
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        $caseType = CaseType::factory()->create(['name' => fake()->unique()->word()]);
        $case = LegalCase::factory()->for($user)->for($client)->for($caseType)->create();

        return [$user, $case];
    }
}
