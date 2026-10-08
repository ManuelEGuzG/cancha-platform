<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerificarProduccion extends Command
{
    protected $signature = 'sportra:verificar-produccion';
    protected $description = 'Comprueba requisitos de seguridad y operación antes de producción';

    public function handle(): int
    {
        $fallos = 0;
        $origins = config('cors.allowed_origins', []);
        $originsSeguros = count($origins) > 0 && collect($origins)->every(
            fn (string $origin) => str_starts_with($origin, 'https://') && !str_contains($origin, '*'),
        );
        $mailDestino = config('backup.notifications.mail.to');
        $claveBackup = (string) config('backup.backup.password');
        $correoOperativo = filter_var($mailDestino, FILTER_VALIDATE_EMAIL) !== false
            && !preg_match('/@example\.(com|net|org)$/i', (string) $mailDestino);
        $claveBackupOperativa = strlen($claveBackup) >= 32
            && !str_contains(strtolower($claveBackup), 'replace-with')
            && !in_array(strtolower($claveBackup), ['password', 'changeme'], true);
        $loggingCentralizado = in_array(config('logging.default'), ['stderr', 'errorlog', 'syslog', 'papertrail'], true);
        $administradores = User::query()->where('is_platform_admin', true)->count();

        $fallos += $this->comprobar('APP_ENV=production', app()->environment('production'));
        $fallos += $this->comprobar('APP_DEBUG=false', !config('app.debug'));
        $fallos += $this->comprobar('APP_KEY configurada', (bool) config('app.key'));
        $fallos += $this->comprobar('APP_URL usa HTTPS', str_starts_with((string) config('app.url'), 'https://'));
        $fallos += $this->comprobar('HTTPS redirigido en el edge', (bool) config('security.https_enforced_at_edge'));
        $fallos += $this->comprobar('CSP aplicada en el edge', (bool) config('security.csp_enforced_at_edge'));
        $fallos += $this->comprobar('DB de producción no es SQLite', in_array(config('database.default'), ['mysql', 'mariadb', 'pgsql'], true));
        $fallos += $this->comprobar('Cookie de sesión Secure', (bool) config('session.secure'));
        $fallos += $this->comprobar('CORS contiene solo orígenes HTTPS específicos', $originsSeguros);
        $fallos += $this->comprobar('Token Sanctum tiene vencimiento', (int) config('sanctum.expiration') > 0);
        $fallos += $this->comprobar('Turnstile tiene secret y hostname', (bool) config('services.turnstile.secret') && (bool) config('services.turnstile.hostname'));
        $fallos += $this->comprobar('Logs enviados a sink centralizado', $loggingCentralizado);
        $fallos += $this->comprobar('Cola asíncrona habilitada', config('queue.default') !== 'sync');
        $fallos += $this->comprobar('Backup apunta a S3', config('backup.backup.destination.disks') === ['s3']);
        $fallos += $this->comprobar('Bucket y credenciales S3 configurados', (bool) config('filesystems.disks.s3.bucket')
            && (bool) config('filesystems.disks.s3.key') && (bool) config('filesystems.disks.s3.secret'));
        $fallos += $this->comprobar('Archivo de backup cifrado con clave no placeholder', $claveBackupOperativa);
        $fallos += $this->comprobar('Transporte de email no es log', !in_array(config('mail.default'), ['log', 'array'], true));
        $fallos += $this->comprobar('Email de alertas real configurado', $correoOperativo);
        $fallos += $this->comprobar('Existe al menos un administrador', $administradores > 0);

        $administradoresSinDosFactores = User::query()
            ->where('is_platform_admin', true)
            ->whereNull('two_factor_enabled_at')
            ->count();

        if ($administradoresSinDosFactores > 0) {
            $this->warn("[WARN] {$administradoresSinDosFactores} administrador(es) aún no activan 2FA.");
        } else {
            $this->info('[OK] Todos los administradores tienen 2FA activo.');
        }

        if ($fallos > 0) {
            $this->error("Preflight no aprobado: {$fallos} requisito(s) bloqueante(s). Revise el runbook de producción.");

            return self::FAILURE;
        }

        $this->info('Preflight aprobado. Confirme también TLS, scheduler, workers y prueba de restauración en infraestructura.');

        return self::SUCCESS;
    }

    private function comprobar(string $nombre, bool $correcto): int
    {
        if (!$correcto) {
            $this->line("[FAIL] {$nombre}");

            return 1;
        }

        $this->line("[OK] {$nombre}");

        return 0;
    }
}