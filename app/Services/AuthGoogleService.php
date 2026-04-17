<?php

namespace Services;

use RuntimeException;

class AuthGoogleService
{
    private array $cfg;

    public function __construct()
    {
        $this->cfg = require __DIR__ . '/../../config/google.php';
    }

   
    public function buildAuthUrl(string $state): string
    {
        $params = http_build_query([
            'client_id'     => $this->cfg['client_id'],
            'redirect_uri'  => $this->cfg['redirect_uri'],
            'response_type' => 'code',
            'scope'         => implode(' ', $this->cfg['scopes']),
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ]);

        return $this->cfg['auth_url'] . '?' . $params;
    }

    public function handleCallback(string $code): array
    {
        $tokenData = $this->exchangeCode($code);
        return $this->fetchUserInfo($tokenData['access_token']);
    }

    // ── Private helpers ──────────────────────────────────────

    private function exchangeCode(string $code): array
    {
        $payload = http_build_query([
            'code'          => $code,
            'client_id'     => $this->cfg['client_id'],
            'client_secret' => $this->cfg['client_secret'],
            'redirect_uri'  => $this->cfg['redirect_uri'],
            'grant_type'    => 'authorization_code',
        ]);

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 10,
            ],
        ]);

        $body = @file_get_contents($this->cfg['token_url'], false, $ctx);
        if ($body === false) {
            throw new RuntimeException('Cannot reach Google token endpoint');
        }

        $data = json_decode($body, true);
        if (!isset($data['access_token'])) {
            throw new RuntimeException('Token exchange failed: ' . ($data['error_description'] ?? $body));
        }

        return $data;
    }

    private function fetchUserInfo(string $accessToken): array
    {
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'header'  => "Authorization: Bearer $accessToken\r\n",
                'timeout' => 10,
            ],
        ]);

        $body = @file_get_contents($this->cfg['userinfo_url'], false, $ctx);
        if ($body === false) {
            throw new RuntimeException('Cannot reach Google userinfo endpoint');
        }

        $info = json_decode($body, true);
        if (empty($info['sub'])) {
            throw new RuntimeException('Invalid userinfo response from Google');
        }

        return [
            'id'             => $info['sub'],
            'email'          => $info['email']          ?? '',
            'name'           => $info['name']           ?? '',
            'picture'        => $info['picture']        ?? '',
            'email_verified' => (bool)($info['email_verified'] ?? false),
        ];
    }
}