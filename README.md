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
- **Login throttling**: 5 attempts per username+IP before lockout (Breeze's
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

## Tools

The sidebar has two tools, both modeled on Google Ads' own Keyword Planner:

- **Discover New Keywords** (`/discover`) — start from seed keywords or a
  website URL and get keyword ideas with avg. monthly searches, competition
  (Low/Medium/High + index), and a low/high CPC range.
- **Keyword Planner** (`/planner`) — paste a keyword list and a max CPC bid,
  get a forecast (impressions, clicks, cost, avg. CPC) per keyword and in
  total; change the bid and resubmit to see the forecast respond.

Both currently run on **sample data** — clearly labeled as such in the UI —
generated deterministically from each keyword's text (`App\Services\GoogleAds\
Support\DeterministicKeywordMetrics`), not real Google Ads figures. See
"Connecting the real Google Ads API" below to switch to live data.

### Connecting the real Google Ads API

`App\Services\GoogleAds\KeywordIdeaGenerator` and `KeywordForecaster` are
interfaces; `AppServiceProvider::register()` currently binds them to the
sample implementations. To go live, implement both against Google's API and
swap the bindings — nothing else in the app (controllers, requests, views)
needs to change.

What you need, in order:

1. **A Google Ads account.** If you don't have one: https://ads.google.com.
2. **A Google Ads Manager (MCC) account**, linked to the account(s) you want
   data for: https://ads.google.com/home/tools/manager-accounts/. The
   Developer Token in step 3 is issued at the Manager account level.
3. **A Developer Token.** Inside the Manager account: Tools & Settings →
   Setup → API Center. A new token starts at **Test account access**
   (works only against Google Ads test accounts) — you must apply for
   **Basic** or **Standard** access to use it against real accounts, which
   Google reviews manually and can take from a few days to a few weeks.
4. **A Google Cloud project with OAuth 2.0 credentials.** In
   https://console.cloud.google.com: create a project, enable the
   "Google Ads API", then create an OAuth 2.0 Client ID + Client Secret
   (Desktop app type is simplest for the one-time refresh-token step next).
5. **A refresh token.** Using the Client ID/Secret, run the OAuth consent
   flow once as a user with access to the Manager account. Google's official
   client libraries ship a script for this
   (`generate_user_credentials` in `googleads/google-ads-php`); it opens a
   browser, you approve access, and it prints a long-lived refresh token.
6. **Customer IDs.** The 10-digit Google Ads customer ID of the Manager
   account (used as `login-customer-id`) and of the specific client account
   being queried.
7. **The official PHP client library**: `composer require googleads/google-ads-php`.
   It handles the API's gRPC/protobuf transport and OAuth token refresh.

You'll end up with five values to put in `.env`: developer token, OAuth
client ID, OAuth client secret, refresh token, and login customer ID.

The specific calls to make once that's wired up:
- Discover New Keywords → `KeywordPlanIdeaService.GenerateKeywordIdeas`
- Keyword Planner forecasts → `KeywordPlanService`'s forecast-metrics calls

Method names/fields can shift between API versions — verify against
Google's current docs before implementing:
https://developers.google.com/google-ads/api/docs/keyword-planning/overview

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
self-registration. `ADMIN_USERNAME` is the login ID; `ADMIN_EMAIL` only
receives password-reset links:

```bash
ADMIN_NAME="Your Name"
ADMIN_USERNAME=admin
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
