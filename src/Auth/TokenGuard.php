<?php

namespace Codelocks\Identity\Auth;

use Codelocks\Identity\Contracts\StoreTokenUser;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class TokenGuard implements Guard
{

    use GuardHelpers;

    /**
     * The name of the query string item from the request containing the API token.
     *
     * @var string
     */
    protected string $inputKey;

    /**
     * Create a new authentication guard.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $inputKey
     * @return void
     */
    public function __construct(
        protected Request $request,
        protected string  $name,
        protected array   $config,
    )
    {
        $this->inputKey = data_get($config, 'inputKey', 'token');
    }

    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $token = $this->getTokenForRequest();

        if (!empty($token)) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $token"
            ])->get(data_get($this->config, 'host') . data_get($this->config, 'user_url'));
            $rawUser  = $response->json();
            if (!Arr::has($rawUser, ['id', 'email'])) {
                abort(500, __('missing authorized user id and email'));
            }
            $userInstance = app(StoreTokenUser::class);
            $user         = $userInstance->findAuthorizedUser($rawUser);
        }

        return $this->user = $user;
    }

    public function validate(array $credentials = [])
    {
        return !is_null((new static(
            $credentials['request'], $this->name, $this->config
        ))->user());
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest(): ?string
    {
        $token = $this->request->query($this->inputKey);

        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
        }

        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        return $token;
    }
}