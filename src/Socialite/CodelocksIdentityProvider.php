<?php

namespace Codelocks\Identity\Socialite;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class CodelocksIdentityProvider extends AbstractProvider implements ProviderInterface
{
    public function getScopes()
    {
        return explode($this->scopeSeparator, config('identity.scopes'));
    }

    protected $scopeSeparator = ' ';


    protected function getHost()
    {
        return config('identity.host');
    }

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getHost() . config('identity.authorize_url'), $state);
    }

    protected function getTokenUrl()
    {
        return $this->getHost() . config('identity.token_url');
    }

    protected function getUserByToken($token)
    {
        $userUrl = $this->getHost() . config('identity.user_url');

        $response = $this->getHttpClient()->get(
            $userUrl,
            $this->getRequestOptions($token)
        );

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map($user);
    }

    protected function getRequestOptions($token)
    {
        return [
            RequestOptions::HEADERS => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ];
    }
}
