<?php

namespace Core;

class MessageCipher
{
    private const CIPHER  = 'aes-256-gcm';
    private const TAG_LEN = 16; 

    private static ?string $key = null;

    // ── Key derivation ─────────────────────────────────────────
    private static function key(): string
    {
        if (self::$key !== null) return self::$key;

        $master = getenv('APP_ENCRYPTION_KEY') ?: ($_ENV['APP_ENCRYPTION_KEY'] ?? '');
        if (strlen($master) < 16) {
            throw new \RuntimeException('APP_ENCRYPTION_KEY is not set or too short (min 16 chars)');
        }

        // HKDF-SHA256 to derive a 32-byte AES key
        self::$key = hash_hkdf('sha256', $master, 32, 'chat-messages-v1');
        return self::$key;
    }

    public static function encrypt(string $plaintext): array
    {
        $iv  = random_bytes(12);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            self::key(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LEN
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed: ' . openssl_error_string());
        }

        return [
            'enc' => base64_encode($ciphertext),
            'iv'  => bin2hex($iv),
            'tag' => bin2hex($tag),
        ];
    }

    // ── Decrypt ────────────────────────────────────────────────
    public static function decrypt(string $enc, string $iv, string $tag): string
    {
        $plaintext = openssl_decrypt(
            base64_decode($enc),
            self::CIPHER,
            self::key(),
            OPENSSL_RAW_DATA,
            hex2bin($iv),
            hex2bin($tag)
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed – authentication error');
        }

        return $plaintext;
    }

    // ── Convenience: encrypt + pack into flat array ────────────
    public static function encryptMessage(string $text): array
    {
        return self::encrypt($text);
    }

    public static function decryptMessage(string $enc, string $iv, string $tag): string
    {
        return self::decrypt($enc, $iv, $tag);
    }
}