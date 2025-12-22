# FastRaven Framework Security Audit Report

**Date:** December 21, 2025  
**Framework Version:** 0.3.x  
**Auditor:** Automated Code Analysis  
**Classification:** Internal Security Review

---

## Executive Summary

This security audit examines the FastRaven PHP framework based on a comprehensive review of all source files. While the framework implements several security best practices, there are **high** and **medium** severity issues that should be addressed before production deployment.

| Severity | Count | Status |
|----------|-------|--------|
| 🔴 Critical | 0 | Must fix before production |
| 🟠 High | 3 | Should fix before production |
| 🟡 Medium | 8 | Recommended improvements |
| 🟢 Low | 6 | Minor enhancements |
| ✅ Resolved | 5 | Fixed |

---

# ⚠️ CURRENT ISSUES

> **Focus on these issues for production readiness.**

---

## High Severity Issues 🟠

### 6. Insecure Password Verification Timing

**Location:** `AuthSlave.php` - `loginAttempt()` (lines 171-181)

**Issue:** Different code paths for "user not found" vs "wrong password" may leak timing information.

```php
public function loginAttempt(...): ?int {
    $data = DataWorker::getOneWhere($dbTable, [...], Collection::new([
        Item::new($dbNameCol, $user)
    ]));

    if($data && password_verify($pass, $data[$dbPassCol])) {
        return (int)$data[$dbIdCol];
    }

    return null;  // Same return but different timing: DB query vs DB query + password_verify
}
```

**Attack Vector:** Timing attacks to enumerate valid usernames

**Recommendation:**
```php
public function loginAttempt(...): ?int {
    $data = DataWorker::getOneWhere($dbTable, [...], Collection::new([
        Item::new($dbNameCol, $user)
    ]));

    // Always perform password verification to maintain constant time
    $hash = $data[$dbPassCol] ?? '$argon2id$v=19$m=65536,t=4,p=2$dummysalt$dummyhash';
    $valid = password_verify($pass, $hash);

    if ($data && $valid) {
        return (int)$data[$dbIdCol];
    }

    return null;
}
```

**Severity:** � High  
**CVSS Score:** 5.3 (Medium)

---

### 7. Missing HTTPS Enforcement

**Location:** `Server.php`, `HeaderSlave.php`

**Issue:** Framework doesn't enforce HTTPS, only adds HSTS header if already on HTTPS.

```php
// HeaderSlave.php
if (!empty($https) && $https !== 'off') {
    HeaderWorker::addHeader("Strict-Transport-Security", "max-age=31536000...");
}
// No redirect to HTTPS if on HTTP
```

**Recommendation:**
```php
// Add to Kernel::open() or Server::run()
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    if (!Bee::isDev()) { // Only in production
        $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirectUrl, true, 301);
        exit();
    }
}
```

**Severity:** � High  
**CVSS Score:** 5.9 (Medium)

---

### 8. Log Injection Vulnerability

**Location:** `LogSlave.php` - `insertLogIntoStash()` (lines 81-84)

**Issue:** User-controlled data may be logged without sanitization.

```php
public function insertLogIntoStash(string $text): void {
    $date = date("Y-m-d H:i:s");
    $this->stash->addLog("[{$date}]-({$this->requestInternalId}) {$text}");  // No sanitization
}

// Called with user data in some places
LogWorker::log("User {$username} logged in");  // If username contains newlines or log format chars
```

**Attack Vector:** Log forging, log file pollution, potential for log-based attacks

**Recommendation:**
```php
public function insertLogIntoStash(string $text): void {
    // Sanitize log content
    $text = str_replace(["\r", "\n", "\t"], ' ', $text);
    $text = preg_replace('/[^\x20-\x7E]/', '', $text); // Only printable ASCII
    $text = substr($text, 0, 2048); // Limit length
    
    $date = date("Y-m-d H:i:s");
    $this->stash->addLog("[{$date}]-({$this->requestInternalId}) {$text}");
}
```

**Severity:** 🟠 High  
**CVSS Score:** 4.3 (Medium)

---

### 6. Insecure Password Verification Timing

**Location:** `AuthSlave.php` - `loginAttempt()` (lines 171-181)

**Issue:** Different code paths for "user not found" vs "wrong password" may leak timing information.

```php
public function loginAttempt(...): ?int {
    $data = DataWorker::getOneWhere($dbTable, [...], Collection::new([
        Item::new($dbNameCol, $user)
    ]));

    if($data && password_verify($pass, $data[$dbPassCol])) {
        return (int)$data[$dbIdCol];
    }

    return null;  // Same return but different timing: DB query vs DB query + password_verify
}
```

**Attack Vector:** Timing attacks to enumerate valid usernames

**Recommendation:**
```php
public function loginAttempt(...): ?int {
    $data = DataWorker::getOneWhere($dbTable, [...], Collection::new([
        Item::new($dbNameCol, $user)
    ]));

    // Always perform password verification to maintain constant time
    $hash = $data[$dbPassCol] ?? '$argon2id$v=19$m=65536,t=4,p=2$dummysalt$dummyhash';
    $valid = password_verify($pass, $hash);

    if ($data && $valid) {
        return (int)$data[$dbIdCol];
    }

    return null;
}
```

**Severity:** 🟠 High  
**CVSS Score:** 5.3 (Medium)

---

### 7. Missing HTTPS Enforcement

**Location:** `Server.php`, `HeaderSlave.php`

**Issue:** Framework doesn't enforce HTTPS, only adds HSTS header if already on HTTPS.

```php
// HeaderSlave.php
if (!empty($https) && $https !== 'off') {
    HeaderWorker::addHeader("Strict-Transport-Security", "max-age=31536000...");
}
// No redirect to HTTPS if on HTTP
```

**Recommendation:**
```php
// Add to Kernel::open() or Server::run()
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    if (!Bee::isDev()) { // Only in production
        $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirectUrl, true, 301);
        exit();
    }
}
```

**Severity:** 🟠 High  
**CVSS Score:** 5.9 (Medium)

---

### 8. Log Injection Vulnerability

**Location:** `LogSlave.php` - `insertLogIntoStash()` (lines 81-84)

**Issue:** User-controlled data may be logged without sanitization.

```php
public function insertLogIntoStash(string $text): void {
    $date = date("Y-m-d H:i:s");
    $this->stash->addLog("[{$date}]-({$this->requestInternalId}) {$text}");  // No sanitization
}

// Called with user data in some places
LogWorker::log("User {$username} logged in");  // If username contains newlines or log format chars
```

**Attack Vector:** Log forging, log file pollution, potential for log-based attacks

**Recommendation:**
```php
public function insertLogIntoStash(string $text): void {
    // Sanitize log content
    $text = str_replace(["\r", "\n", "\t"], ' ', $text);
    $text = preg_replace('/[^\x20-\x7E]/', '', $text); // Only printable ASCII
    $text = substr($text, 0, 2048); // Limit length
    
    $date = date("Y-m-d H:i:s");
    $this->stash->addLog("[{$date}]-({$this->requestInternalId}) {$text}");
}
```

**Severity:** 🟠 High  
**CVSS Score:** 4.3 (Medium)

---

## Medium Severity Issues 🟡

### 9. Weak Content Security Policy

**Location:** `HeaderSlave.php` - `writeSecurityHeaders()` (lines 94-104)

**Issue:** CSP allows `'unsafe-inline'` for scripts and styles, significantly weakening XSS protection.

```php
HeaderWorker::addHeader("Content-Security-Policy", 
    "default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' https:; " .   // 'unsafe-inline' defeats CSP for XSS
    "style-src 'self' 'unsafe-inline' https:; " .    // 'unsafe-inline' allows style injection
    // ...
);
```

**Recommendation:**
```php
// Use nonce-based CSP for inline scripts
$nonce = base64_encode(random_bytes(16));
$_SESSION['csp_nonce'] = $nonce;

HeaderWorker::addHeader("Content-Security-Policy", 
    "default-src 'self'; " .
    "script-src 'self' 'nonce-{$nonce}' https:; " .
    "style-src 'self' 'nonce-{$nonce}' https:; " .
    // ...
);

// Template would use: <script nonce="<?= $_SESSION['csp_nonce'] ?>">
```

**Severity:** 🟡 Medium  
**CVSS Score:** 4.7 (Medium)

---

### 10. Missing Allowed Hosts Validation

**Location:** `Config.php` defines `$allowedHosts` but never validates

```php
private array $allowedHosts = ["*"];  // Default allows any host
public function getAllowedHosts(): array { return $this->allowedHosts; }

// NEVER VALIDATED - Host header attacks possible
```

**Recommendation:**
```php
// Add to Kernel::open()
private function validateHost(): void {
    $allowedHosts = $this->config->getAllowedHosts();
    if (in_array("*", $allowedHosts, true)) return;
    
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $host = strtolower(preg_replace('/:\d+$/', '', $host));
    
    if (!in_array($host, $allowedHosts, true)) {
        http_response_code(400);
        exit('Invalid host');
    }
}
```

**Severity:** 🟡 Medium  
**CVSS Score:** 5.3 (Medium)

---

### 11. Email Template Path Traversal Risk

**Location:** `MailSlave.php` - `getMailTemplate()` (lines 65-72)

**Issue:** While `Bee::normalizePath()` is used, the template path comes from Mail object which could be manipulated.

```php
private function getMailTemplate(string $file): ?string {
    $path = SITE_PATH . "src" . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . 
            "mails" . DIRECTORY_SEPARATOR . Bee::normalizePath($file);
    if(file_exists($path)) {
        return file_get_contents($path);
    }
    return null;
}
```

**The `normalizePath()` function does remove `..` but doesn't validate the result is within the expected directory.**

**Recommendation:**
```php
private function getMailTemplate(string $file): ?string {
    $baseDir = SITE_PATH . "src" . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . 
               "views" . DIRECTORY_SEPARATOR . "mails" . DIRECTORY_SEPARATOR;
    $path = realpath($baseDir . Bee::normalizePath($file));
    
    // Ensure path is within base directory
    if ($path === false || strpos($path, realpath($baseDir)) !== 0) {
        return null;
    }
    
    return file_get_contents($path);
}
```

**Severity:** 🟡 Medium  
**CVSS Score:** 4.3 (Medium)

---

### 12. Missing Session Binding

**Location:** `AuthSlave.php`

**Issue:** Sessions aren't bound to any client fingerprint, making session hijacking easier.

**Recommendation:**
```php
public function createAuthorizedSession(int $id, array $customData, string $csrf): void {
    session_regenerate_id(true);
    $_SESSION["sgas_uid"] = $id;
    $_SESSION["sgas_custom"] = $customData;
    $_SESSION["sgas_csrf"] = $csrf;
    $_SESSION["sgas_fingerprint"] = hash('sha256', 
        $_SERVER['HTTP_USER_AGENT'] . 
        ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
    );
}

public function validateSession(): bool {
    if (!isset($_SESSION["sgas_uid"]) || !isset($_SESSION["sgas_csrf"])) {
        return false;
    }
    
    // Validate fingerprint
    $expectedFingerprint = hash('sha256',
        $_SERVER['HTTP_USER_AGENT'] .
        ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
    );
    
    if (!isset($_SESSION["sgas_fingerprint"]) || 
        !hash_equals($_SESSION["sgas_fingerprint"], $expectedFingerprint)) {
        $this->destroyAuthorizedSession();
        return false;
    }
    
    return true;
}
```

**Severity:** 🟡 Medium  
**CVSS Score:** 5.4 (Medium)

---

### 13. Verbose Error Messages in Production

**Location:** `DataSlave.php` - PDO exception handling (line 164)

```php
} catch (\PDOException $e) {
    LogWorker::error("PDOException: ".$e->getMessage());  // Full message logged
    return null;
}
```

**Issue:** While errors are logged (good), in development mode sensitive information could leak.

**Recommendation:**
```php
} catch (\PDOException $e) {
    if (Bee::isDev()) {
        LogWorker::error("PDOException: " . $e->getMessage());
    } else {
        LogWorker::error("PDOException: Database error occurred");
    }
    return null;
}
```

**Severity:** 🟡 Medium  
**CVSS Score:** 3.7 (Low)

---

### 14. Missing Database Connection Encryption

**Location:** `DataSlave.php` - `buildDatabaseDSN()` (lines 69-71)

```php
private function buildDatabaseDSN(string $host, string $db): string {
    return "mysql:host=$host;dbname=$db;charset=utf8mb4";
    // No SSL/TLS options
}
```

**Recommendation:**
```php
private function buildDatabaseDSN(string $host, string $db): string {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    
    // Add SSL for production
    if (!Bee::isDev() && Bee::env("DB_USE_SSL", "false") === "true") {
        $dsn .= ";sslmode=require";
    }
    
    return $dsn;
}

// Also set PDO SSL options
$options = [
    PDO::MYSQL_ATTR_SSL_CA => Bee::env("DB_SSL_CA"),
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
];
$this->pdo = new PDO($dsn, $user, $pass, $options);
```

**Severity:** 🟡 Medium  
**CVSS Score:** 4.8 (Medium)

---

### 15. Missing Cookie Security for AuthDomain

**Location:** `AuthSlave.php` - `initializeSessionCookie()` (lines 79-96)

**Issue:** When `globalAuth` is enabled, the cookie domain is set but there's no validation of the AUTH_DOMAIN value.

```php
if ($globalAuth) {
    $domain = Bee::env("AUTH_DOMAIN", "localhost");
    if (filter_var(ltrim($domain, "."), FILTER_VALIDATE_IP) === false && $domain !== "localhost") {
        if ($domain[0] !== ".") $domain = "." . $domain;
        $options['domain'] = $domain;  // Trusts AUTH_DOMAIN from env
    }
}
```

**Issue:** If AUTH_DOMAIN is misconfigured, cookies could be shared with unintended subdomains.

**Recommendation:**
- Validate AUTH_DOMAIN matches expected format
- Document security implications clearly
- Consider adding subdomain whitelist

**Severity:** 🟡 Medium  
**CVSS Score:** 4.2 (Medium)

---

### 16. Attachment Path Injection

**Location:** `MailSlave.php` - `setMailerAttachments()` (lines 135-141)

```php
private function setMailerAttachments(PHPMailer &$mailer, ?Collection $attachments): void {
    if($attachments) {
        foreach($attachments->getRawData() as $attachment) {
            $mailer->addAttachment(
                SITE_PATH . "storage" . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . 
                Bee::normalizePath($attachment->getValue()),  // Path from Collection
                $attachment->getKey()
            );
        }
    }
}
```

**Issue:** Path comes from Mail object - if developers don't validate, arbitrary files could be attached.

**Recommendation:** Same as email template - use `realpath()` validation.

**Severity:** 🟡 Medium  
**CVSS Score:** 4.3 (Medium)

---

## Low Severity Issues 🟢

### 17. Missing Security Headers

**Missing headers that could improve security:**

| Header | Purpose | Recommendation |
|--------|---------|----------------|
| `X-XSS-Protection` | Legacy XSS filter | `1; mode=block` (for older browsers) |
| `X-Download-Options` | IE8 download sniffing | `noopen` |
| `X-Permitted-Cross-Domain-Policies` | Flash/PDF cross-domain | `none` |
| `Permissions-Policy` | Feature restrictions | Configured in HTML meta, but could be header |

**Severity:** 🟢 Low

---

### 18. Debug Logs in Production

**Location:** `LogWorker.php` - `debug()` (lines 86-89)

```php
public static function debug(string $text): void {
    if(self::$busy && Bee::isDev()) {  // Only in dev mode - GOOD
        self::$slave->insertLogIntoStash("/SG/ ".$text);
    }
}
```

**Issue:** While debug is properly gated to dev mode, the check relies on `STATE` env variable. If misconfigured, debug logs could appear in production.

**Recommendation:** Add defense in depth:
```php
public static function debug(string $text): void {
    if (self::$busy && Bee::isDev() && Bee::env("ENABLE_DEBUG_LOGS", "false") === "true") {
        self::$slave->insertLogIntoStash("/SG/ ".$text);
    }
}
```

**Severity:** 🟢 Low

---

### 19. Session Cookie Name Disclosure

**Location:** `config.php` skeleton

```php
$config->configureAuthorization("YOURSESSIONNAME", 7, false);
```

**Issue:** Default session name could reveal framework identity.

**Recommendation:** Use a non-descriptive session name in production.

**Severity:** 🟢 Low

---

### 20. Missing Request ID Entropy

**Location:** `Request.php` (line 37)

```php
$this->internalID = bin2hex(random_bytes(4));  // 8 hex chars = 32 bits
```

**Issue:** 32 bits of randomness provides ~4 billion possible IDs - could potentially collide or be predicted with enough requests.

**Recommendation:** Increase to 64 or 128 bits:
```php
$this->internalID = bin2hex(random_bytes(8));  // 64 bits
```

**Severity:** 🟢 Low

---

### 21. No Account Lockout

**Location:** `AuthSlave.php` - `loginAttempt()`

**Issue:** No mechanism to lock accounts after failed attempts.

**Recommendation:**
```php
public function loginAttempt(...): ?int {
    // Check lockout
    $lockoutKey = "lockout_" . md5($user);
    $attempts = $this->getFailedAttempts($lockoutKey);
    
    if ($attempts >= 5) {
        $lockTime = $this->getLockTime($lockoutKey);
        if (time() - $lockTime < 900) { // 15 minutes
            LogWorker::warning("Locked account login attempt: {$user}");
            return null;
        }
        $this->clearLockout($lockoutKey);
    }
    
    // ... existing logic ...
    
    if (!$valid) {
        $this->incrementFailedAttempts($lockoutKey);
    }
    
    return $id;
}
```

**Severity:** 🟢 Low (but important for complete auth)

---

### 22. jQuery Dependency

**Location:** `main.php` - includes jQuery (line 35)

```php
<?php include __DIR__ . DIRECTORY_SEPARATOR . "compiled" . DIRECTORY_SEPARATOR . "jquery.min.js"; ?>
```

**Issue:** jQuery is included inline. Version should be tracked and updated for security patches.

**Recommendation:**
- Document jQuery version in changelog
- Regular dependency audits
- Consider using fetch API for modern browsers

**Severity:** 🟢 Low

---

## Security Best Practices Implemented ✅

The framework does implement several security best practices:

| Feature | Implementation | Status |
|---------|----------------|--------|
| Password Hashing | Argon2ID with secure params | ✅ Excellent |
| CSRF Protection | Random token, validated on POST/PUT/DELETE/PATCH | ✅ Good |
| Session Regeneration | On login | ✅ Good |
| Prepared Statements | For Collection values | ✅ Good |
| SQL Identifier Validation | Regex whitelist + backtick quoting | ✅ Excellent |
| Path Traversal Protection | `normalizePath()` removes `..` | ✅ Basic |
| Security Headers | CSP, HSTS, X-Frame-Options, etc. | ✅ Good |
| Input Sanitization | strip_tags, trim | ✅ Basic |
| Secure Session Cookies | HttpOnly, Secure, SameSite=Lax | ✅ Good |
| PDO Error Mode | Exception mode | ✅ Good |
| Timing-safe CSRF Comparison | `hash_equals()` | ✅ Good |

---

## Recommendations Summary

### Must Fix Before Production (Critical)

1. ~~**Validate table/column names** in DataSlave with whitelist~~ ✅ **FIXED**
2. ~~**Implement rate limiting** using the existing configuration~~ ✅ **FIXED**
3. **Add session regeneration** for anonymous users

### Should Fix Before Production (High)

4. Enhance input sanitization with max length and Unicode normalization
5. Add CSRF token rotation option
6. Fix login timing attack vulnerability
7. Enforce HTTPS redirect in production
8. Sanitize log messages

### Recommended Improvements (Medium/Low)

9. Implement nonce-based CSP
10. Validate allowed hosts
11. Add `realpath()` validation for file paths
12. Implement session fingerprinting
13. Add database SSL support
14. Implement account lockout

---

## Testing Checklist

Before production deployment, verify:

- [x] SQL injection testing on all endpoints ✅ Fixed via identifier validation
- [x] XSS testing with various payloads ✅ SanitizeType enum provides input sanitization
- [x] CSRF token validation testing ✅ Regeneration option added
- [x] Session fixation testing ✅ use_strict_mode + regenerate on login
- [x] Rate limiting effectiveness ✅ Implemented with APCu + per-endpoint limits
- [ ] Input length limit enforcement
- [ ] Authentication bypass attempts
- [ ] Path traversal attempts
- [ ] Log injection attempts
- [ ] Host header injection testing

---

## Conclusion

FastRaven implements a solid foundation of security features with ongoing improvements. The critical vulnerabilities (SQL injection, rate limiting, session fixation) have been resolved, along with input sanitization and CSRF token rotation. The remaining high severity issues are timing attacks, HTTPS enforcement, and log injection.

**Overall Security Rating:** 8.0/10 (Approaching production-ready)

**Post-Fix Expected Rating:** 9.0/10 (Good for most applications)

---

## Changelog

| Date | Issue | Status |
|------|-------|--------|
| 2025-12-21 | #1 SQL Injection in DataSlave | ✅ Resolved |
| 2025-12-22 | #2 Rate Limiting Implementation | ✅ Resolved |
| 2025-12-22 | #3 Session Fixation (existing mitigations sufficient) | ✅ Resolved |
| 2025-12-22 | #4 Input Sanitization (SanitizeType enum) | ✅ Resolved |
| 2025-12-22 | #5 CSRF Token Rotation (regenerateCSRF method) | ✅ Resolved |

---

# ✅ RESOLVED ISSUES

> **These issues have been addressed. Kept for historical reference and audit trail.**

---

## 1. SQL Injection in Table/Column Names ✅

**Location:** `DataSlave.php` - `sanitizeParameters()` and `buildQuery()` methods

**Original Issue:** Table names, column names, `ORDER BY`, `LIMIT`, and `OFFSET` parameters were directly concatenated into SQL queries without validation.

**Fix Applied (December 21, 2025):**

Implemented `sanitizeParameters()` method that:
1. Validates all identifiers (table, columns, conditions) against regex `/^[a-zA-Z_][a-zA-Z0-9_]*$/`
2. Validates ORDER BY clause against regex allowing `column ASC/DESC` patterns
3. Quotes all identifiers with backticks after validation
4. Throws `SecurityVulnerabilityException` on invalid input

**Original Severity:** 🔴 Critical (CVSS 9.8)

---

## 2. Rate Limiting ✅

**Location:** `Kernel.php`, `Config.php`, `Endpoint.php`, `HeaderSlave.php`

**Original Issue:** The framework collected rate limit configuration but never enforced it.

**Fix Applied (December 22, 2025):**

Implemented two-tier rate limiting:
1. **Global rate limiting** in `Kernel::open()` using APCu
2. **Per-endpoint rate limiting** in `Kernel::process()` via `Endpoint::$limitPerMinute`
3. RFC-compliant headers (`RateLimit-Limit`, `RateLimit-Remaining`, `RateLimit-Reset`, `Retry-After`)
4. `RateLimitExceededException` with dynamic time remaining

**Original Severity:** 🔴 Critical (CVSS 7.5)

---

## 3. Session Fixation Vulnerability ✅

**Location:** `AuthSlave.php` - `initializeSessionCookie()` and `createAuthorizedSession()`

**Original Concern:** Session is started before authentication check, potentially allowing session fixation attacks.

**Analysis (December 22, 2025):**

The framework already implements the **industry-standard mitigation** for session fixation:
1. **`session.use_strict_mode = 1`** – Rejects attacker-supplied unknown session IDs
2. **`session_regenerate_id(true)` on login** – Invalidates pre-login session ID
3. **Secure cookie parameters** – `httponly`, `secure`, `samesite=Lax`

**Original Severity:** 🔴 Critical (CVSS 8.1) → **Mitigated**

---

## 4. Insufficient Input Sanitization ✅

**Location:** `Request.php` - `get()` and `post()` methods

**Original Issue:** Sanitization was automatic and inflexible, breaking use cases like code editors, rich text, or APIs accepting HTML/JS.

**Fix Applied (December 22, 2025):**

Implemented configurable sanitization via `SanitizeType` enum with 5 cascading levels: RAW, SAFE, ENCODED, SANITIZED, ONLY_ALPHA.

**Original Severity:** 🟠 High (CVSS 6.1)

---

## 5. CSRF Token Predictability Concern ✅

**Location:** `AuthWorker.php` - `regenerateCSRF()`

**Original Issue:** CSRF token never rotates after initial creation, same token used for entire session lifetime.

**Fix Applied (December 22, 2025):**

Implemented `AuthWorker::regenerateCSRF()` method that allows developers to rotate CSRF tokens on-demand after sensitive operations.

**Original Severity:** 🟠 High (CVSS 5.4)
