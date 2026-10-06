<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_preflight_fails_closed_on_local_configuration(): void
    {
        config([
            'app.env' => 'testing',
            'app.debug' => true,
            'app.url' => 'http://localhost',
            'database.default' => 'sqlite',
            'session.secure' => false,
            'cors.allowed_origins' => ['http://localhost:5173'],
            'sanctum.expiration' => null,
            'services.turnstile.secret' => null,
            'services.turnstile.hostname' => null,
            'queue.default' => 'sync',
            'backup.backup.destination.disks' => ['local'],
            'filesystems.disks.s3.bucket' => null,
            'filesystems.disks.s3.key' => null,
            'filesystems.disks.s3.secret' => null,
            'backup.backup.password' => null,
            'backup.notifications.mail.to' => 'backup@example.com',
        ]);

        $this->assertSame(1, Artisan::call('sportra:verificar-produccion'));
        $this->assertStringContainsString('[FAIL] APP_DEBUG=false', Artisan::output());
    }
}