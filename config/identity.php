<?php

return [
    'provider_name' => env('CODELOCKS_PROVIDER_NAME', 'Codelocks Identity'),
    'client_id'     => env('CODELOCKS_CLIENT_ID'),
    'client_secret' => env('CODELOCKS_CLIENT_SECRET'),
    'redirect'      => env('CODELOCKS_OAUTH_REDIRECT', route('identity.callback')),
    'home'          => env('CODELOCKS_CLIENT_HOME', env('APP_URL') . '/home'),

    'host'          => env('CODELOCKS_OAUTH_HOST'),
    'authorize_url' => env('CODELOCKS_AUTHORIZE_HOST', '/oauth/authorize'),
    'token_url'     => env('CODELOCKS_TOKEN_HOST', '/oauth/token'),
    'refresh_url'   => env('CODELOCKS_REFRESH_TOKEN_HOST', '/oauth/token/refresh'),
    'user_url'      => env('CODELOCKS_TOKEN_HOST', '/api/user/profile'),
    'scopes'        => env('CODELOCKS_TOKEN_SCOPES', '*'),

    'input_key' => 'token',
    'routes'    => [
        'redirect' => '/auth/redirect',
        'callback' => '/auth/callback'
    ]
];