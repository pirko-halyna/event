# Medium Severity Fixes — Auth & Password Reset

## Fixed Medium Severity Issues

### 1. Password reset does not invalidate existing JWTs

**Root cause:** `PasswordResetService::resetPassword()` updated the user's password but left all previously issued JWTs valid until their natural expiry or refresh window (up to 14 days), meaning a hijacked session survived a password reset.

**Fix:** On successful password reset, write a `jwt_valid_after:{userId}` key to the Redis cache with a value of `now()->timestamp` and a TTL equal to `refresh_ttl` minutes. `AuthService::authenticateToken()` reads this key after parsing the JWT and rejects any token whose `iat` (issued-at) claim predates the recorded timestamp. The TTL is self-managing — once all pre-reset tokens would have expired naturally the key is gone too.

Changed files:
- `app/Services/PasswordResetService.php` — captures `$userId` inside the transaction, writes `Cache::put("jwt_valid_after:{$userId}", now()->timestamp, ...)` after commit
- `app/Services/AuthService.php` — new `authenticateToken()` method performs the cache check
- `app/Http/Middleware/AuthTokenMiddleware.php` — now calls `authenticateToken()` instead of `getUserByToken()`

---

### 2. Long effective token lifetime via `refresh_ttl`

**Root cause:** `config/jwt.php` defaulted `refresh_ttl` to `20160` minutes (14 days) — the same value as `remember_me_ttl`. This meant a regular 60-minute access token could be refreshed for two weeks, making the short TTL effectively meaningless and extending the revocation window to the maximum.

**Fix:** Reduced the default `refresh_ttl` to `1440` minutes (24 hours). The `remember_me_ttl` remains at 14 days for remember-me sessions. Both values can be overridden per environment via `JWT_REFRESH_TTL` and `JWT_REMEMBER_ME_TTL`.

Changed files:
- `config/jwt.php`

---

### 3. Custom `AuthTokenMiddleware` diverged from framework guard semantics

**Root cause:** The middleware called `AuthService::getUserByToken()` which used `JWTAuth::setToken()->toUser()` (user only, no token stored on guard), then called `auth()->setUser($user)` to bind the user. This left the JWT guard without a token, so `auth()->getToken()`, `auth()->logout()` (blacklist), and `auth()->payload()` did not work correctly downstream.

**Fix:** Replaced `getUserByToken()` with a new `AuthService::authenticateToken()` method that calls `JWTAuth::setToken($token)->authenticate()`. This method both validates the token and sets the user on the underlying guard (via the auth provider's `byId()` call), making the full JWT guard contract available to all downstream code. The middleware then calls `auth()->setUser($user)` as a guard-layer backstop so the user is accessible via `auth()->user()` even in test contexts where the service is mocked.

The `getUserByToken()` method is retained in `AuthService` to avoid breaking any external callers.

Changed files:
- `app/Services/AuthService.php` — added `authenticateToken()`
- `app/Http/Middleware/AuthTokenMiddleware.php` — calls `authenticateToken()`, retains `auth()->setUser()`

---

### 4. Weak password policy / no breach check

**Root cause:** Both `RegisterRequest` and `NewPasswordRequest` only enforced `min:8` with no complexity requirement and no check against known data-breach databases. `NewPasswordRequest` also lacked a `max:72` upper bound, meaning bcrypt would silently truncate passwords longer than 72 bytes.

**Fix:** Replaced the plain `min:8` string rule with Laravel's `Password` rule object configured to require mixed case, at least one number, a maximum of 72 bytes (bcrypt limit), and a Have I Been Pwned breach check.

```php
['required', 'string', 'max:72', 'confirmed', Password::min(8)->mixedCase()->numbers()->uncompromised()]
```

Changed files:
- `app/Http/Requests/Auth/RegisterRequest.php`
- `app/Http/Requests/Auth/NewPasswordRequest.php`

---

## Code Changes

### `app/Services/AuthService.php` — new `authenticateToken` method (replaces `getUserByToken` call from middleware)

```php
public function authenticateToken(string $token): ?User
{
    try {
        $user = JWTAuth::setToken($token)->authenticate();
    } catch (JWTException $e) {
        Log::warning('JWT authentication failed.', ['message' => $e->getMessage()]);
        return null;
    }

    if (!$user) {
        return null;
    }

    $validAfter = Cache::get("jwt_valid_after:{$user->id}");
    $issuedAt   = JWTAuth::getPayload()->get('iat');

    if ($validAfter !== null && $issuedAt < $validAfter) {
        return null;
    }

    return $user;
}
```

### `app/Services/PasswordResetService.php` — invalidate tokens on reset

```php
$userId = null;

DB::transaction(function () use ($email, $code, $newPassword, &$userId) {
    // ... existing code ...
    $userId = User::where('email', $email)->value('id');
    User::where('email', $email)->update(['password' => Hash::make($newPassword)]);
});

if ($userId !== null) {
    $refreshTtl = (int) config('jwt.refresh_ttl', 20160);
    Cache::put("jwt_valid_after:{$userId}", now()->timestamp, now()->addMinutes($refreshTtl));
}
```

### `app/Http/Middleware/AuthTokenMiddleware.php` — uses `authenticateToken`

```php
$user = $this->authService->authenticateToken($token);

if (!$user) {
    return response()->json(['message' => 'Unauthorized'], 401);
}

auth()->setUser($user);
```

### `config/jwt.php`

```diff
- 'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),
+ 'refresh_ttl' => env('JWT_REFRESH_TTL', 1440),
```

### `app/Http/Requests/Auth/RegisterRequest.php` and `NewPasswordRequest.php`

```diff
- 'password' => 'required|string|min:8|max:72|confirmed',
+ 'password' => ['required', 'string', 'max:72', 'confirmed', Password::min(8)->mixedCase()->numbers()->uncompromised()],
```

---

## Security Notes

- **Token invalidation uses existing Redis infrastructure.** No schema change was needed. The `jwt_valid_after` cache key has a TTL equal to `refresh_ttl` so it is self-pruning once all pre-reset tokens have expired.

- **The cache check is in `AuthService`, not in the middleware.** Keeps the middleware thin (HTTP layer only) and the authentication logic testable in isolation.

- **`JWTAuth::authenticate()` vs `toUser()`.** The new flow uses `authenticate()` which wires the token into the JWT guard, enabling `auth()->logout()` (blacklisting) and `auth()->payload()` to work correctly in controllers.

- **`refresh_ttl` reduction (20160 → 1440 minutes).** Reduces the window in which a stolen non-remember-me token can be refreshed from 14 days to 24 hours. Override via `JWT_REFRESH_TTL` in `.env` if a longer window is required for specific clients.

- **`uncompromised()` makes an HTTPS call to the Have I Been Pwned range API.** Tests fake this endpoint with `Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)])` so they are not flaky in offline/CI environments.

- **`max:72` added to `NewPasswordRequest`.** Closes the silent bcrypt truncation gap where a password set during reset could differ from what the user believed they set if it exceeded 72 bytes.
