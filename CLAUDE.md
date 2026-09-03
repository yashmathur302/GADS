# GADS — Laravel Admin Panel: Standing Development Standards

This file is the permanent technical foundation for this project. It applies to
**every** future feature, migration, controller, view, and PR — not just the
initial scaffold. When new functionality is requested, combine it with these
standards; never discard or silently relax them for convenience.

## 1. Project shape

- Laravel (latest stable) + PHP + MySQL + Blade + Vite + Tailwind CSS.
- **Single admin user.** No roles, no permissions table, no RBAC. Do not add
  role/permission scaffolding unless explicitly requested.
- Do not invent business modules, tables, dashboards, reports, or CRUD screens.
  Functionality is provided separately; only build what's been asked for.

## 2. Security posture (non-negotiable)

- Authorization is **always** server-side. A hidden button, disabled field, JS
  check, obfuscated URL, or UUID is never a substitute for a real authorization
  check on the request.
- Every resource access (view/update/delete/download/export) must verify the
  authenticated admin is allowed to touch that specific record — never assume
  "has an ID" means "has permission."
- Never build SQL from unsanitized input; use Eloquent/Query Builder
  parameter binding only.
- Never mass-assign `$request->all()` into a model. Validate via Form Requests
  and assign explicit fields (or `$request->validated()`).
- All user input is validated server-side via Form Request classes — frontend
  validation is UX only, never a security control.
- Blade's default escaping stays on. No raw `{!! !!}` output of untrusted data.
  Any future rich text goes through a strict HTML sanitizer/allowlist.
- CSRF protection stays enabled globally; no blanket exemptions.
- Rate limiting on login, password reset, and any other sensitive/expensive
  endpoint added later.
- File uploads (if/when added): validate MIME + extension server-side,
  randomize stored filenames, store outside public exposure unless intended
  public, enforce size limits, never trust client-supplied metadata.
- Passwords: Laravel's built-in hashing only, `Password::defaults()` policy
  (min length + complexity + breach check), never logged/exposed, secure
  reset-token flow with expiration.
- Sessions: HttpOnly + SameSite + Secure (in production) cookies, session
  regeneration on login, invalidation + regeneration on logout.
- `APP_DEBUG=false` in production; no stack traces, queries, paths, or env
  values ever reach a response. Friendly 403/404/419/429/500 pages.
- Security headers (CSP, X-Content-Type-Options, frame-ancestors/X-Frame-
  Options, Referrer-Policy, Permissions-Policy, HSTS over HTTPS) applied via
  middleware — only what the app actually needs, nothing that breaks it.
- Log security-relevant events (login success/failure, logout, password
  change/reset, sensitive admin actions) — never log passwords, tokens, keys,
  or secrets.
- No secrets committed. `.env` stays out of git; `.env.example` has no real
  values.

## 3. Architecture conventions

- Thin controllers. Push validation into Form Requests, business logic into
  Services/Actions, authorization into Policies, response shaping into
  Resources — but only introduce these when the operation's complexity
  actually warrants it. Don't create a Service class for a one-line query.
- Descriptive names: `CustomerController`, `CreateCustomerRequest`,
  `CustomerService`, `CustomerPolicy` — never `DataController`, `Helper`,
  `process()`, `handleData()`.
- Migrations for every schema change — no manual production schema edits.
  Proper foreign keys, indexes, unique constraints, and data types.
- Keep dependencies minimal: before adding a package, confirm Laravel doesn't
  already cover it, and check maintenance/security/compatibility first.

## 4. Frontend conventions

- Semantic HTML (`header`, `nav`, `main`, `section`, `aside`, `footer`,
  `form`, `label`, `button`), logical heading hierarchy, no heading-for-
  styling.
- Reusable Blade components for repeated chrome (layout shell, sidebar,
  header/nav, alerts, cards, buttons, tables, pagination, modals) — don't
  duplicate markup, don't build a component for something used once.
- Tailwind-based design tokens (spacing/typography/color) kept consistent;
  avoid ad hoc inline styles and `!important`.
- Responsive by design (desktop/laptop/tablet/mobile), not a shrunk desktop
  layout — sidebar, tables, forms, and modals all need explicit small-screen
  behavior.
- Accessibility: keyboard operability, visible focus states, labeled form
  controls, accessible validation messaging, logical tab order, accessible
  modals/dialogs, ARIA only when semantic HTML isn't enough.

## 5. Process for new functionality

For every feature request that arrives after this scaffold:
1. Understand the requirement as stated — don't expand scope.
2. Design the data/routes/controller/request/policy/view shape before coding.
3. Threat-model it: tampering, IDOR, injection, privilege escalation, file
   attacks, race conditions, enumeration — for that specific feature.
4. Implement cleanly, following the conventions above.
5. Test both the happy path and malicious/invalid input (auth, authz,
   validation, IDOR, injection, XSS, CSRF, rate limits as applicable).
6. Review for security, performance (N+1s, missing eager loading/pagination/
   indexes), code quality, responsiveness, and accessibility before calling
   it done.

Do not treat this file as boilerplate to skim past — it is the contract for
how this codebase is built.
