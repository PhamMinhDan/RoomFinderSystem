<?php

namespace Core;

class SessionManager
{
    private const SESSION_NAME  = 'RF_SESSION';
    private const USER_KEY      = 'auth_user';
    private const CSRF_KEY      = 'csrf_token';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(self::SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 3600),
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),  
                'httponly' => true,                      
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    // ── Auth user ────────────────────────────────────────────
    public static function setUser(array $user): void
    {
        self::start();
        $_SESSION[self::USER_KEY] = $user;
    }

    public static function getUser(): ?array
    {
        self::start();
        return $_SESSION[self::USER_KEY] ?? null;
    }

    public static function isLoggedIn(): bool
    {
        return self::getUser() !== null;
    }

    public static function logout(): void
    {
        self::start();
        unset($_SESSION[self::USER_KEY]);
        session_destroy();
        setcookie(self::SESSION_NAME, '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }

    // ── CSRF token ───────────────────────────────────────────
    public static function getCsrfToken(): string
    {
        self::start();
        if (empty($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CSRF_KEY];
    }

    public static function verifyCsrf(string $token): bool
    {
        self::start();
        $stored = $_SESSION[self::CSRF_KEY] ?? '';
        return hash_equals($stored, $token);
    }

    // ── OAuth state (chống CSRF redirect) ────────────────────
    public static function setOAuthState(string $state): void
    {
        self::start();
        $_SESSION['oauth_state'] = $state;
    }

    public static function verifyOAuthState(string $state): bool
    {
        self::start();
        $stored = $_SESSION['oauth_state'] ?? '';
        unset($_SESSION['oauth_state']); // dùng một lần
        return hash_equals($stored, $state);
    }
}