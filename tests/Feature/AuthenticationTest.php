<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Matheus Soares',
            'email' => 'MATHEUS@EXAMPLE.COM',
            'password' => 'cliente123',
            'password_confirmation' => 'cliente123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Matheus Soares',
            'email' => 'matheus@example.com',
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'advogado@example.com',
            'password' => 'cliente123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'ADVOGADO@EXAMPLE.COM',
            'password' => 'cliente123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'advogado@example.com',
            'password' => 'cliente123',
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'senha-incorreta',
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Maria Advocacia',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Maria Advocacia')
            ->assertSee('Clientes cadastrados')
            ->assertSee('Casos cadastrados');
    }
}
