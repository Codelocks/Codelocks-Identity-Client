<?php

return [
    'provider_name' => env('CODELOCKS_PROVIDER_NAME', 'Codelocks Identity'),
    'client_id'     => env('CODELOCKS_CLIENT_ID'),
    'client_secret' => env('CODELOCKS_CLIENT_SECRET'),
    'redirect'      => env('CODELOCKS_OAUTH_REDIRECT', env('APP_URL') . '/auth/callback'),
    'home'          => env('CODELOCKS_CLIENT_HOME', env('APP_URL') . '/home'),

    'host'          => env('CODELOCKS_OAUTH_HOST', 'https://my.codelocksconnect.net'),
    'authorize_url' => env('CODELOCKS_AUTHORIZE_URL', '/oauth/authorize'),
    'token_url'     => env('CODELOCKS_TOKEN_URL', '/oauth/token'),
    'refresh_url'   => env('CODELOCKS_REFRESH_TOKEN_URL', '/oauth/token/refresh'),
    'user_url'      => env('CODELOCKS_TOKEN_URL', '/api/user/profile'),
    'scopes'        => env('CODELOCKS_TOKEN_SCOPES', 'profile profile.current_team'),

    'input_key' => 'token',
    'routes'    => [
        'redirect' => '/auth/redirect',
        'callback' => '/auth/callback'
    ],
    'cookie'    => 'codelocks_cookie'
];
