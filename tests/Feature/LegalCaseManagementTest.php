<?php

namespace Tests\Feature;

use App\Models\CaseType;
use App\Models\Client;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalCaseManagementTest extends TestCase
{
    use RefreshDatabase;

    private CaseType $caseType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseType = CaseType::factory()->create([
            'name' => 'Família',
        ]);
    }

    public function test_guest_cannot_access_cases(): void
    {
        $this->get(route('casos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_clients_is_sent_to_client_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('casos.create'))
            ->assertRedirect(route('clientes.create'))
            ->assertSessionHas('warning');
    }

    public function test_user_can_create_case_for_own_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('casos.store'), [
            'client_id' => $client->id,
            'case_type_id' => $this->caseType->id,
            'title' => '  Ação de alimentos  ',
            'description' => 'Pedido de alimentos provisórios.',
            'status' => LegalCase::STATUS_NOVO_ATENDIMENTO,
            'opened_at' => '',
            'closed_at' => '',
        ]);

        $case = $user->legalCases()->firstOrFail();

        $response->assertRedirect(route('casos.show', $case));
        $this->assertDatabaseHas('legal_cases', [
            'id' => $case->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Ação de alimentos',
            'opened_at' => now()->startOfDay()->toDateTimeString(),
        ]);
    }

    public function test_user_cannot_create_case_for_another_users_client(): void
    {
        $user = User::factory()->create();
        $otherClient = Client::factory()->create();

        $response = $this->actingAs($user)->post(route('casos.store'), [
            'client_id' => $otherClient->id,
            'case_type_id' => $this->caseType->id,
            'title' => 'Caso indevido',
            'status' => LegalCase::STATUS_NOVO_ATENDIMENTO,
        ]);

        $response->assertSessionHasErrors('client_id');
        $this->assertDatabaseCount('legal_cases', 0);
    }

    public function test_user_only_sees_own_cases(): void
    {
        $user = User::factory()->create();
        $ownClient = Client::factory()->for($user)->create();
        $otherCase = LegalCase::factory()->create([
            'title' => 'Caso de outro escritório',
            'case_type_id' => $this->caseType->id,
        ]);
        LegalCase::factory()->for($user)->for($ownClient)->create([
            'title' => 'Caso visível',
            'case_type_id' => $this->caseType->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('casos.index'));

        $response
            ->assertOk()
            ->assertSee('Caso visível')
            ->assertDontSee($otherCase->title);
    }

    public function test_user_cannot_access_another_users_case(): void
    {
        $user = User::factory()->create();
        $ownClient = Client::factory()->for($user)->create();
        $otherCase = LegalCase::factory()->create([
            'case_type_id' => $this->caseType->id,
        ]);

        $this->actingAs($user)
            ->get(route('casos.show', $otherCase))
            ->assertNotFound();

        $this->actingAs($user)
            ->put(route('casos.update', $otherCase), $this->validCaseData($ownClient))
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('casos.whatsapp', $otherCase))
            ->assertNotFound();
    }

    public function test_finishing_case_sets_closed_date(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        $case = LegalCase::factory()->for($user)->for($client)->create([
            'case_type_id' => $this->caseType->id,
        ]);

        $response = $this->actingAs($user)->put(
            route('casos.update', $case),
            $this->validCaseData($client, LegalCase::STATUS_FINALIZADO),
        );

        $response->assertRedirect(route('casos.show', $case));
        $this->assertDatabaseHas('legal_cases', [
            'id' => $case->id,
            'status' => LegalCase::STATUS_FINALIZADO,
            'closed_at' => now()->startOfDay()->toDateTimeString(),
        ]);
    }

    public function test_reopening_case_clears_closed_date(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        $case = LegalCase::factory()->for($user)->for($client)->create([
            'case_type_id' => $this->caseType->id,
            'status' => LegalCase::STATUS_FINALIZADO,
            'closed_at' => now()->toDateString(),
        ]);

        $this->actingAs($user)->put(
            route('casos.update', $case),
            $this->validCaseData($client, LegalCase::STATUS_EM_ANALISE),
        );

        $this->assertDatabaseHas('legal_cases', [
            'id' => $case->id,
            'status' => LegalCase::STATUS_EM_ANALISE,
            'closed_at' => null,
        ]);
    }

    public function test_whatsapp_redirect_contains_phone_and_case_status(): void
    {
        $user = User::factory()->create(['name' => 'Dra. Maria']);
        $client = Client::factory()->for($user)->create([
            'name' => 'João da Silva',
            'phone' => '(15) 99999-0000',
        ]);
        $case = LegalCase::factory()->for($user)->for($client)->create([
            'case_type_id' => $this->caseType->id,
            'title' => 'Revisão de contrato',
            'status' => LegalCase::STATUS_EM_ANALISE,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('casos.whatsapp', $case));

        $location = (string) $response->headers->get('Location');

        $response->assertRedirect();
        $this->assertStringStartsWith('https://wa.me/5515999990000?text=', $location);
        $this->assertStringContainsString(rawurlencode('Em análise'), $location);
        $this->assertStringContainsString(rawurlencode('Revisão de contrato'), $location);
    }

    public function test_whatsapp_requires_client_phone(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create(['phone' => null]);
        $case = LegalCase::factory()->for($user)->for($client)->create([
            'case_type_id' => $this->caseType->id,
        ]);

        $this->actingAs($user)
            ->from(route('casos.show', $case))
            ->get(route('casos.whatsapp', $case))
            ->assertRedirect(route('casos.show', $case))
            ->assertSessionHasErrors('whatsapp');
    }

    /**
     * @return array<string, mixed>
     */
    private function validCaseData(Client $client, string $status = LegalCase::STATUS_NOVO_ATENDIMENTO): array
    {
        return [
            'client_id' => $client->id,
            'case_type_id' => $this->caseType->id,
            'title' => 'Caso atualizado',
            'description' => null,
            'status' => $status,
            'opened_at' => now()->toDateString(),
            'closed_at' => null,
        ];
    }
}
