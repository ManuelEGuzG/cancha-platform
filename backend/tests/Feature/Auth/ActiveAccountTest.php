<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ActiveAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_account_cannot_use_an_existing_token(): void
    {
        $user = User::factory()->create(['activo' => false]);
        $token = $user->createToken('inactive-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertForbidden();
    }

    public function test_disabling_an_account_revokes_its_existing_tokens(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $owner = User::factory()->create();
        $adminToken = $admin->createToken('admin-test')->plainTextToken;
        $ownerToken = $owner->createToken('owner-test')->plainTextToken;

        $this->withToken($adminToken)
            ->patchJson("/api/panel/admin/usuarios/{$owner->id}/estado")
            ->assertOk();

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'activo' => false]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $owner->id,
            'name' => 'owner-test',
        ]);

        $this->assertNull(PersonalAccessToken::findToken($ownerToken));
    }

    public function test_api_responses_include_security_headers_and_allow_configured_frontend_origin(): void
    {
        $this->withHeader('Origin', 'http://localhost:5173')
            ->getJson('/api/geografia/provincias')
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'none'; object-src 'none'; base-uri 'self'");

    }

    public function test_secure_requests_receive_hsts(): void
    {
        $request = Request::create('https://sportra.test/api/geografia/provincias');
        $response = (new SecurityHeaders)->handle($request, fn () => new Response);

        $this->assertSame(
            'max-age=31536000; includeSubDomains',
            $response->headers->get('Strict-Transport-Security'),
        );
    }

    public function test_platform_admin_cannot_read_owner_reservation_endpoint(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $token = $admin->createToken('admin-reservations-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/panel/reservas')
            ->assertForbidden();
    }

    public function test_api_login_returns_non_cacheable_bearer_without_session_cookie(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonStructure(['token', 'user']);
        $this->assertFalse($response->headers->has('Set-Cookie'));
    }
}