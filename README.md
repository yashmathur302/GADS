# GADS — Laravel Admin Panel

A Laravel admin panel scaffold built to a security-first standard, for a
single authorized admin user. This is the foundational scaffold only —
business functionality is added incrementally on top of it; see `CLAUDE.md`
for the standards every future feature must follow.

## Stack

- Laravel 13, PHP 8.4
- MySQL (production/dev), SQLite (used automatically in the test suite)
- Blade + Laravel Breeze (auth scaffolding, registration removed)
- Vite + Tailwind CSS

## What's implemented so far

This scaffold intentionally contains no business modules. It establishes:

- **Authentication** for a single admin account: login, logout, password
  reset, password confirmation, password change. No public registration route.
- **Password policy**: 12+ characters, mixed case, numbers, symbols, and a
  breach check (`Password::defaults()` in `AppServiceProvider`).
- **Login throttling**: 5 attempts per email+IP before lockout (Breeze's
  `LoginRequest`); password-reset endpoints are throttled too.
- **Security headers** on every response (`app/Http/Middleware/SecurityHeaders.php`):
  CSP, X-Content-Type-Options, X-Frame-Options, Referrer-Policy,
  Permissions-Policy, and HSTS when served over HTTPS.
- **Security event logging** (`storage/logs/security.log`, `security` log
  channel): login, failed login, lockout, logout, password reset. No
  secrets are ever logged.
- **Session security**: database-backed sessions (server-side revocation),
  HttpOnly + SameSite cookies, `SESSION_SECURE_COOKIE` for production HTTPS.
- **Custom error pages** for 403/404/419/429/500/503 that reveal nothing
  about the underlying error.
- **Admin UI shell**: reusable Blade components for the layout, sidebar,
  header, footer, breadcrumbs, alerts, and cards
  (`resources/views/components/admin/`).

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure your MySQL connection in `.env` (`DB_*`), then:

```bash
php artisan migrate
```

Set the single admin's credentials in `.env` before seeding — there is no
self-registration:

```bash
ADMIN_NAME="Your Name"
ADMIN_EMAIL=you@example.com
ADMIN_PASSWORD="a-strong-unique-password"
```

```bash
php artisan db:seed
npm install
npm run build   # or `npm run dev` while developing
php artisan serve
```

## Testing

```bash
php artisan test
```

Covers authentication, rate limiting, unauthorized access, profile
authorization, and the presence of security headers — not just the happy
path.

## Production checklist

- `APP_DEBUG=false`, `APP_ENV=production`
- `SESSION_SECURE_COOKIE=true` (requires HTTPS)
- MySQL user scoped to least privilege needed by this app
- `.env` never committed; secrets injected via environment/secret manager
- `storage/` and `bootstrap/cache/` writable by the web server only
