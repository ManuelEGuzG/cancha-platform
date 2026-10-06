# Sportra Production Runbook

This checklist is for deployment configuration. The application repository cannot provision cloud resources or verify external services without the production environment.

## Required Environment

- Use `APP_ENV=production`, `APP_DEBUG=false`, a unique `APP_KEY`, and an `APP_URL` beginning with `https://`.
- Use MySQL/MariaDB or PostgreSQL with TLS enabled, `SESSION_SECURE_COOKIE=true`, and `SANCTUM_EXPIRATION` set to a finite duration.
- Set `CORS_ALLOWED_ORIGINS` to exact HTTPS frontend origins. Do not use wildcard origins.
- Set `HTTPS_ENFORCED_AT_EDGE=true` and `CSP_ENFORCED_AT_EDGE=true` only after the proxy redirects HTTP and sends the CSP to the frontend document.
- Set `TURNSTILE_SECRET` and `TURNSTILE_HOSTNAME` for the deployed frontend host. Public reservations fail closed in production if the verification secret is absent.
- Configure an asynchronous queue and run `php artisan queue:work --tries=3`. Configure a shared Reverb service for all application instances.
- Configure `BACKUP_DISK=s3`, the S3 bucket/region/access credentials, a randomly generated `BACKUP_ARCHIVE_PASSWORD` of at least 32 characters, and `BACKUP_NOTIFICATION_EMAIL`.
- Set `LOG_CHANNEL=stderr` or a configured central sink. Configure `MAIL_MAILER` for backup failure notifications.
- Replace the local CORS, Reverb, and example values in `.env.example`; never deploy those example values.

## TLS, Headers, And Proxy

Terminate TLS at the load balancer/reverse proxy, redirect HTTP to HTTPS there, and configure Laravel to trust only the actual proxy addresses. The API middleware sends HSTS on requests Laravel recognizes as secure, plus `X-Frame-Options`, `X-Content-Type-Options`, and a baseline CSP.

The frontend is served separately from Laravel. Configure the edge/web server to add a CSP to the HTML document. Allow only the deployed API and Reverb hosts in `connect-src`; allow Cloudflare Turnstile, Google Fonts, and the image hosts actually used by the frontend. Keep `frame-ancestors 'none'`, `object-src 'none'`, and `base-uri 'self'`. Do not copy a policy with placeholder domains into production.

## Administrator 2FA

1. An administrator signs in with email/password while 2FA is not yet enabled.
2. Call `POST /api/auth/2fa/setup` with that bearer token and the current password. Scan the returned `otpauth_url`; never put the returned secret in logs or tickets.
3. Call `POST /api/auth/2fa/confirm` with a current six-digit TOTP. Store the returned recovery codes offline; they are shown only once. Existing tokens are revoked after activation.
4. Sign in again with email/password and `code`. A recovery code can replace the TOTP once. Disable 2FA only with current password plus a TOTP/recovery code.

The setup/confirm/disable endpoints are API-only until the frontend account-security view is designed. Keep administrator access to this operation restricted and verify TOTP enrollment before granting production admin access.

## Scheduler And Workers

Run one scheduler process or install the Laravel scheduler cron entry once per deployment:

```cron
* * * * * cd /srv/sportra/backend && php artisan schedule:run >> /dev/null 2>&1
```

The schedule expires pending requests every minute, purges personal data older than `RESERVATION_PII_RETENTION_DAYS` (default 365), and runs backup, cleanup, and backup-health checks daily. Run queue workers and Reverb as supervised long-lived processes; use shared cache/queue backends for horizontal scaling.

## Backups And Restore

Backups run daily at 02:00, cleanup at 02:45, and health monitoring at 03:00. They exclude `.env`, use archive encryption, verify the archive, and target the configured S3 disk. Add an S3 lifecycle rule that expires old backup objects no later than the approved PII retention period; versioning/object-lock policies must also respect that period.

Before go-live, run `php artisan backup:run`, `php artisan backup:list`, and `php artisan backup:monitor` in the production environment. Perform a restore drill into an isolated database and verify row counts and application boot. Backups containing database records are sensitive even when the live reservation PII has been purged.

## Go-Live Checks

Run `php artisan sportra:verificar-produccion`. Resolve every `[FAIL]`, activate TOTP for all platform administrators, confirm the scheduler and queue worker are running, and test both TLS headers and the CORS allowlist from the actual frontend origin. The command does not validate external DNS, firewall rules, S3 lifecycle, SMTP delivery, actual CSP response headers, or MySQL concurrency; verify those directly in the deployment environment.

## Charging Policy

Monthly reports expose gross reservation activity using the stored reservation-time price. They intentionally do not calculate a platform fee until the owner charging policy is decided (fixed CRC 30,000/month, 1%, or another rule).