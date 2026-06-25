# Code Review: Authentication & Password Reset

## Scope

This review covers the authentication and password-reset implementation of the **eventHub** Laravel 11 API (JWT via `tymon/jwt-auth`). Files reviewed:

- `app/Http/Controllers/AuthController.php`
- `app/Services/AuthService.php`
- `app/Services/PasswordResetService.php`
- `app/Http/Middleware/AuthTokenMiddleware.php`
- `app/Http/Requests/Auth/*` (Login, Register, PasswordReset, NewPassword)
- `app/Models/User.php`, `app/Models/PasswordResetCode.php`
- `app/Mail/PasswordResetMail.php` + `resources/views/emails/auth/password-reset.blade.php`
- `app/Console/Commands/DeletePasswordResetTokens.php`
- `app/Providers/AppServiceProvider.php` (rate limiters)
- `config/auth.php`, `config/jwt.php`, `routes/api.php`, `bootstrap/app.php`
- `database/migrations/2026_06_24_000001_replace_password_reset_with_codes_table.php`

---

## Authentication

### Login Logic Correctness

- `AuthController::login()` cleanly delegates to `AuthService::login()`, passing only `email` and `password` plus the `remember_me` flag. Good separation.
- `AuthService::login()` correctly sets a per-attempt TTL (`auth()->setTTL($ttl)->attempt($credentials)`) and throws `AuthenticationException` on failure. The exception is rendered as a generic `401 Unauthorized` in `bootstrap/app.php` — **no enumeration leak**. 👍
- **Concern:** `remember_me_ttl` defaults to `20160` minutes (14 days), which is identical to `refresh_ttl`. A normal (non-remember) token has a 60-minute `ttl` but can still be *refreshed* for up to 14 days via `refresh_ttl`, so the effective revocation window is two weeks regardless of `remember_me`. This weakens the value of the short `ttl`.

### Password Validation and Hashing

- Hashing relies on the Eloquent `'password' => 'hashed'` cast on `User`, which uses the framework default (bcrypt unless configured for argon2). Hashing is **not** done manually — correct and idiomatic.
- `config/auth.php` sets `'hash' => false` on the `api` guard, which is the required setting for `tymon/jwt-auth` credential checking. Correct.
- **Weak password policy:** `RegisterRequest` only enforces `min:8|max:72`. There is no complexity requirement and no breach check. Laravel's `Password::min(8)->mixedCase()->numbers()->uncompromised()` rule object is the framework-native way to strengthen this.
- **Inconsistency:** `RegisterRequest` caps the password at `max:72` (the bcrypt byte limit) but `NewPasswordRequest` (reset flow) has **no `max` rule**. A password longer than 72 bytes set during reset will be silently truncated by bcrypt, creating a subtle mismatch between the two flows.

### Session / JWT Handling

- Token generation is delegated to the JWT guard — correct.
- `blacklist_enabled` defaults to `true`, so invalidated tokens are rejected. Good.
- **Logout:** `auth()->logout()` works because the JWT guard re-parses the bearer token from the request and blacklists it. This is functional but *implicit* — it depends on the request still carrying the `Authorization` header at controller time, which it does here.
- **Custom middleware reinvents the guard:** `AuthTokenMiddleware` manually pulls the bearer token, resolves the user via `AuthService::getUserByToken()`, then calls `auth()->setUser($user)`. This duplicates what the framework's `auth:api` middleware already does. Because it uses `setUser()` rather than parsing the token into the guard, the guard never holds a token — any downstream code expecting `auth()->getToken()` / refresh semantics will not find one. Prefer the built-in `auth:api` middleware.
- **Password change does not revoke existing tokens:** After `resetPassword()`, previously issued JWTs remain valid until they expire/refresh-expire. There is no per-user token-version claim or mass invalidation, so a compromised session survives a password reset for up to the refresh window.

### Brute-Force Protection (Rate Limiting)

- All sensitive routes are throttled (`routes/api.php`) with named limiters defined in `AppServiceProvider`. Good coverage: `login` (3/min), `register` (10/min), `password-reset-request` (5/min), `password-reset-confirm` (10/min by IP + 5/min by email).
- **Login limiter keys on email alone** (`->by($email ?: $request->ip())`):
  - **Victim lockout / DoS:** An attacker can deliberately exhaust a known victim's 3/min budget, blocking the legitimate user from logging in.
  - **Limit bypass:** An attacker spraying many different emails from one IP is never throttled by IP, since the key rotates per email. Throttle by **both** IP and email (two `Limit` entries).

### Error Handling (Information Leakage)

- Login failures, JWT failures, and not-found all return generic JSON messages — no stack traces or differentiation. 👍
- `AuthService::getUserByToken()` catches `JWTException`, logs a warning (no token value logged), and returns `null`. Good.
- `bootstrap/app.php` registers Sentry; confirm production `APP_DEBUG=false` so framework exception detail is never returned to clients.

---

## Password Reset Flow

### Token / Code Generation

- `generateCode()` uses `random_int(0, 999_999)` — a **cryptographically secure** RNG — zero-padded to 6 digits. Correct choice of RNG.
- **Entropy is low:** a 6-digit numeric code is ~20 bits (1,000,000 values). This is acceptable *only because* of the short TTL and the per-email rate limit; see brute-force note below.

### Storage Strategy

- Codes are stored **hashed** (`Hash::make($code)`), never in plaintext — excellent. Even DB read access does not reveal usable codes.
- TTL is enforced via `expires_at` (15 minutes) and checked in `isExpired()`.
- On a new request, all prior unused codes for the email are invalidated (`used_at` stamped) inside a transaction — good single-active-code policy.

### Email Delivery

- `PasswordResetMail` is **queued** (`Mail::to()->queue(...)`), keeping the request fast and making response timing independent of SMTP latency — this also helps mitigate timing-based enumeration.
- The email template is clean, includes expiry messaging, and a "if you didn't request this, ignore" notice. 👍
- **Note:** Because delivery is queued, a failed mail job is invisible to the caller. Ensure the queue has retry/alerting; otherwise a user may never receive a code while the API reports success.

### Verification Logic & One-Time-Use Enforcement

- `resetPassword()` looks up the latest unused code, then `Hash::check()`s the supplied code. On success it stamps `used_at` and updates the password inside a transaction. One-time use is *intended*.
- **Race condition (TOCTOU):** The flow is *select-then-update* with no row lock. Two concurrent requests carrying the same valid code can both pass the `whereNull('used_at')` read before either writes `used_at`, allowing a code to be consumed twice. Use `->lockForUpdate()` on the select inside the transaction, or perform a conditional `UPDATE ... WHERE used_at IS NULL` and check the affected-row count.

### User Enumeration (Reset)

- `sendResetCode()` computes the code and hash **before** the user lookup and always returns the same generic message (`"If an account with that email exists…"`). The controller response is identical whether or not the account exists. **Well done** — this is the correct pattern.
- `resetPassword()` uses a dummy `Hash::make('dummy')` when no code row exists so `Hash::check()` runs in comparable time regardless. Good constant-time-ish mitigation.
- **Minor:** `resetPassword()` distinguishes "invalid code" from "expired code" in its error messages. This only matters to someone who already holds a valid-but-expired code, so the disclosure is low risk, but a single generic "invalid or expired" message is marginally safer.

---

## Security

### SQL Injection

- All DB access is via Eloquent query builder with bound parameters (`where('email', $email)`, etc.). **No raw SQL, no injection risk.**

### Timing Attacks

- Reset request and confirm paths both perform hashing work on the non-existent-account path, reducing timing oracles. Login uses the framework's constant-time hash check. Good.

### Token Leakage

- Reset codes are hashed at rest and never returned in any API response. The JWT is returned only in the login/register response body (expected for an API). No tokens are logged.

### CSRF

- These are stateless, token-authenticated JSON API routes (`apiPrefix: 'v1'`, Bearer auth) — CSRF protection is not applicable, which is correct. There is no cookie-based session to forge.

### Transport Security (HTTPS)

- The application **assumes** HTTPS but does not enforce it. There is no `->withMiddleware()` rule forcing HTTPS / HSTS, and no `URL::forceScheme('https')`. Bearer tokens and reset codes travel in cleartext if a client ever hits `http://`. Enforce HTTPS at the edge (load balancer) and/or in the app for defense in depth.

---

## Code Quality

### Architecture / Separation of Concerns

- Clear layering: thin controllers → services (`AuthService`, `PasswordResetService`) → models. FormRequests handle validation. This is a clean, testable structure. 👍
- Rate-limiter definitions are grouped into private methods in `AppServiceProvider` — readable.

### Maintainability & Readability

- Code is consistent, typed (return types, readonly promoted constructor props in the Mailable), and uses constants (`EXPIRY_MINUTES`). Good.
- **Reinvented framework features:**
  - `AuthTokenMiddleware` duplicates `auth:api`. Removing it in favor of the built-in guard middleware would reduce surface area and bugs.
  - The custom `password_reset_codes` table + service partially reimplements Laravel's Password Broker. The current approach (numeric code by email) is a legitimate product choice, but note that `config/auth.php` still declares the unused `passwords.users` broker pointing at the now-dropped `password_reset_tokens` table — **dead/misleading config** that should be removed or aligned.

### Naming Conventions

- Names are descriptive and idiomatic (`sendResetCode`, `resetPassword`, `passwordResetConfirm`). The console command class is `DeletePasswordResetTokens` but operates on *codes* — rename to `DeletePasswordResetCodes` for consistency with the new model/table.

### Proper Use of Framework Features

- Good: FormRequest validation, Eloquent casts (`hashed`, `datetime`), queued Mailable, named rate limiters, scheduled cleanup command, model scope `scopeActiveForEmail` (though it appears unused by the service — see edge cases).

---

## Edge Cases

- **Expired tokens:** Handled — `isExpired()` is checked and a dedicated error is thrown.
- **Reused tokens:** Mostly handled via `used_at`, **but** the select-then-update race (see Password Reset section) allows double-use under concurrency.
- **Multiple reset requests:** Handled — each new request invalidates prior unused codes, enforcing a single active code.
- **Concurrent authentication/reset attempts:** Login is safe (idempotent). Reset confirm is **not fully safe** due to the missing row lock.
- **Invalid input handling:** FormRequests validate types/formats; email is normalized (`strtolower(trim())`) in both reset requests — good, prevents case-mismatch lookups. Login `email` uses `email` rule; reset uses stricter `email:rfc`. Minor inconsistency.
- **Unused scope:** `PasswordResetCode::scopeActiveForEmail()` already encapsulates the "unused + not expired" query but the service re-implements the conditions inline. Using the scope would centralize the logic and reduce drift.
- **Cleanup gap:** `DeletePasswordResetTokens` deletes only rows where `expires_at < now()`. Used-but-not-yet-expired codes linger until expiry. Minor data-retention nit; consider also pruning `whereNotNull('used_at')`.

---

## Summary of Issues

### Critical
*None identified.*

### High

- **Login rate limiter keyed only by email**
  - *Description:* `Limit::perMinute(3)->by($email ?: $request->ip())` lets an attacker (a) lock a known victim out by exhausting their per-email budget, and (b) bypass throttling entirely by rotating the email value from a single IP.
  - *Recommendation:* Apply two limits — by normalized email **and** by IP — e.g. `[Limit::perMinute(5)->by('login:'.$email), Limit::perMinute(20)->by($request->ip())]`. Consider exponential backoff/lockout after repeated failures.

- **Reset-code one-time-use race condition (TOCTOU)**
  - *Description:* `resetPassword()` selects the unused code then later writes `used_at` without locking, so concurrent requests with the same code can both succeed.
  - *Recommendation:* Add `->lockForUpdate()` to the select inside the transaction, or use a conditional `UPDATE ... WHERE used_at IS NULL` and verify the affected-row count before proceeding.

### Medium

- **Password reset does not invalidate existing JWTs**
  - *Description:* After a successful reset, previously issued tokens remain valid until expiry/refresh-expiry, so a hijacked session survives the reset.
  - *Recommendation:* Add a per-user token-version (custom JWT claim) bumped on password change, or otherwise blacklist/rotate active tokens on reset.

- **Long effective token lifetime via `refresh_ttl`**
  - *Description:* Non-remember tokens (60-min `ttl`) can be refreshed for up to 14 days (`refresh_ttl`), undermining the short TTL and lengthening the revocation window.
  - *Recommendation:* Reduce `refresh_ttl`, or distinguish remember vs non-remember refresh windows; document the intended session lifetime.

- **Custom `AuthTokenMiddleware` duplicates `auth:api`**
  - *Description:* Reimplements token parsing/user resolution, diverging from framework guard semantics (no token set on the guard) and increasing maintenance/bug surface.
  - *Recommendation:* Use the built-in `auth:api` middleware; delete the custom middleware unless it provides behavior the guard cannot.

- **Weak password policy / no breach check**
  - *Description:* Only `min:8` is enforced; no complexity or compromised-password check.
  - *Recommendation:* Use Laravel's `Password` rule: `Password::min(8)->mixedCase()->numbers()->uncompromised()`.

### Low

- **`max:72` missing on reset password rule** — `NewPasswordRequest` allows >72-byte passwords that bcrypt silently truncates. Add `max:72` to match `RegisterRequest`.
- **HTTPS not enforced in-app** — relies on edge config. Add HSTS / `URL::forceScheme('https')` in production for defense in depth.
- **Enumeration of "invalid" vs "expired" code** — use a single generic "invalid or expired code" message in `resetPassword()`.
- **Dead/misleading config** — `config/auth.php` `passwords.users` broker references the dropped `password_reset_tokens` table. Remove or align with the custom flow.
- **Cleanup command misnamed and incomplete** — `DeletePasswordResetTokens` operates on codes (rename to `…Codes`) and leaves used-but-unexpired rows; also prune `whereNotNull('used_at')`.
- **Inline query duplicates `scopeActiveForEmail`** — reuse the existing model scope to centralize the "active code" definition.
- **Validation inconsistency** — login uses `email` while reset uses `email:rfc`; standardize.
- **Queued reset mail has no caller-visible failure path** — ensure queue retries/alerting so silently dropped emails are detected.

### Positive Highlights

- Reset codes stored **hashed** with TTL and single-active-code invalidation.
- **Cryptographically secure** code generation (`random_int`).
- Strong **user-enumeration mitigation** on both reset endpoints (generic responses + constant-time-style hashing + queued mail).
- Clean **service/controller/request separation** and consistent, typed, readable code.
- Comprehensive **rate-limiter coverage** across all auth endpoints (aside from the login keying issue above).
