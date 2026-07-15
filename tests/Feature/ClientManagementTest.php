<?php

namespace Tests\Feature;

use App\Models\CaseType;
use App\Models\Client;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_clients(): void
    {
        $this->get(route('clientes.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_create_client(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('clientes.store'), [
            'name' => '  Ana Pereira  ',
            'email' => 'ANA@EXAMPLE.COM',
            'phone' => '(11) 99999-0000',
            'document' => '123.456.789-00',
            'notes' => 'Cliente do direito de família.',
        ]);

        $client = $user->clients()->firstOrFail();

        $response->assertRedirect(route('clientes.show', $client));
        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'Ana Pereira',
            'email' => 'ana@example.com',
        ]);
    }

    public function test_user_only_sees_own_clients(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Client::factory()->for($user)->create(['name' => 'Cliente Visível']);
        Client::factory()->for($otherUser)->create(['name' => 'Cliente de Outro Escritório']);

        $response = $this
            ->actingAs($user)
            ->get(route('clientes.index'));

        $response
            ->assertOk()
            ->assertSee('Cliente Visível')
            ->assertDontSee('Cliente de Outro Escritório');
    }

    public function test_user_can_search_own_clients(): void
    {
        $user = User::factory()->create();

        Client::factory()->for($user)->create(['name' => 'Carlos Almeida']);
        Client::factory()->for($user)->create(['name' => 'Fernanda Lima']);

        $response = $this
            ->actingAs($user)
            ->get(route('clientes.index', ['search' => 'Carlos']));

        $response
            ->assertOk()
            ->assertSee('Carlos Almeida')
            ->assertDontSee('Fernanda Lima');
    }

    public function test_user_cannot_access_another_users_client(): void
    {
        $user = User::factory()->create();
        $otherClient = Client::factory()->create();

        $this->actingAs($user)
            ->get(route('clientes.show', $otherClient))
            ->assertNotFound();

        $this->actingAs($user)
            ->put(route('clientes.update', $otherClient), ['name' => 'Nome alterado'])
            ->assertNotFound();

        $this->actingAs($user)
            ->delete(route('clientes.destroy', $otherClient))
            ->assertNotFound();

        $this->assertDatabaseHas('clients', [
            'id' => $otherClient->id,
            'name' => $otherClient->name,
        ]);
    }

    public function test_user_can_update_own_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('clientes.update', $client), [
            'name' => 'Nome Atualizado',
            'email' => 'NOVO@EXAMPLE.COM',
            'phone' => '',
            'document' => '',
            'notes' => '',
        ]);

        $response->assertRedirect(route('clientes.show', $client));
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Nome Atualizado',
            'email' => 'novo@example.com',
            'phone' => null,
        ]);
    }

    public function test_name_is_required_to_create_client(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('clientes.create'))
            ->post(route('clientes.store'), ['name' => '']);

        $response
            ->assertRedirect(route('clientes.create'))
            ->assertSessionHasErrors('name');
        $this->assertDatabaseCount('clients', 0);
    }

    public function test_user_can_delete_client_without_cases(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('clientes.destroy', $client));

        $response->assertRedirect(route('clientes.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_client_with_cases_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        $caseType = CaseType::create(['name' => 'Família']);

        LegalCase::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'case_type_id' => $caseType->id,
            'title' => 'Ação de alimentos',
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('clientes.show', $client))
            ->delete(route('clientes.destroy', $client));

        $response
            ->assertRedirect(route('clientes.show', $client))
            ->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }
}
