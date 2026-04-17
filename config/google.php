<?php

return [
    'client_id'     => $_ENV['GOOGLE_CLIENT_ID']     ?? '',
    'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
    'redirect_uri'  => ($_ENV['BACKEND_URL'] ?? 'http://localhost:8000') . '/auth/google/callback',
    'scopes'        => [
        'openid',
        'email',
        'profile',
    ],
    'auth_url'      => 'https://accounts.google.com/o/oauth2/v2/auth',
    'token_url'     => 'https://oauth2.googleapis.com/token',
    'userinfo_url'  => 'https://www.googleapis.com/oauth2/v3/userinfo',
];