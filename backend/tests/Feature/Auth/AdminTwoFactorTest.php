<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\AdminTwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_must_verify_totp_and_recovery_codes_are_one_time(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $token = $admin->createToken('admin-two-factor-test')->plainTextToken;

        $setup = $this->withToken($token)
            ->postJson('/api/auth/2fa/setup', ['password' => 'password'])
            ->assertOk();
        $secreto = $setup->json('data.secret');

        $recuperacion = $this->withToken($token)
            ->postJson('/api/auth/2fa/confirm', ['code' => app(Google2FA::class)->getCurrentOtp($secreto)])
            ->assertOk()
            ->json('data.recovery_codes.0');
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $admin->id,
            'name' => 'admin-two-factor-test',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertUnprocessable();

        $this->postJson('/api/auth/login', [
            'email' => $admin->email,
            'password' => 'password',
            'code' => app(Google2FA::class)->getCurrentOtp($secreto),
        ])->assertUnprocessable();

        $this->postJson('/api/auth/login', [
            'email' => $admin->email,
            'password' => 'password',
            'code' => $recuperacion,
        ])->assertOk();

        $this->travel(61)->seconds();
        $this->postJson('/api/auth/login', [
            'email' => $admin->email,
            'password' => 'password',
            'code' => $recuperacion,
        ])->assertUnprocessable();
    }

    public function test_same_totp_cannot_be_reused_within_its_time_step(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $totp = app(Google2FA::class);
        $secreto = $totp->generateSecretKey(32);
        $admin->forceFill([
            'two_factor_secret' => $secreto,
            'two_factor_enabled_at' => now(),
            'two_factor_last_used_step' => $totp->getTimestamp() - 1,
            'two_factor_recovery_codes' => [],
        ])->save();
        $codigo = $totp->getCurrentOtp($secreto);
        $service = app(AdminTwoFactorService::class);

        $this->assertTrue($service->verificar($admin->fresh(), $codigo));
        $this->assertFalse($service->verificar($admin->fresh(), $codigo));
    }

    public function test_two_factor_setup_is_admin_only_and_requires_current_password(): void
    {
        $owner = User::factory()->create();
        $token = $owner->createToken('owner-two-factor-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/auth/2fa/setup', ['password' => 'incorrecta'])
            ->assertForbidden();
    }

    public function test_admin_two_factor_setup_requires_current_password(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $adminToken = $admin->createToken('admin-two-factor-test')->plainTextToken;
        $this->withToken($adminToken)
            ->postJson('/api/auth/2fa/setup', ['password' => 'incorrecta'])
            ->assertUnprocessable();
    }
}