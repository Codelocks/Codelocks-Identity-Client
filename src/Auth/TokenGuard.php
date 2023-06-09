<?php

namespace Codelocks\Identity\Auth;

use Codelocks\Identity\Contracts\StoreTokenUser;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Encryption\Encrypter;
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
    protected string  $inputKey;
    private Encrypter $encryptor;

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
        $this->inputKey  = data_get($config, 'inputKey', 'token');
        $this->encryptor = new Encrypter(config('app.key'));
    }

    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }


        $token = $this->getTokenForRequest();

        if (!empty($token)) {
            return $this->user = $this->getTokenUser($token);
        } elseif ($this->request->cookie(config('identity.cookie'))) {
            return $this->user = $this->authenticateViaCookie($this->request);
        }

    }

    private function getTokenUser($token)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $token"
        ])->get(data_get($this->config, 'host') . data_get($this->config, 'user_url'));
        $rawUser  = $response->json();
        if (!Arr::has($rawUser, ['id', 'email'])) {
            abort(500, __('missing authorized user id and email'));
        }
        $userInstance = app(StoreTokenUser::class);
        return $userInstance->findAuthorizedUser($rawUser);
    }

    public function validate(array $credentials = [])
    {
        return !is_null((new static(
            $credentials['request'],
            $this->name,
            $this->config
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

    private function authenticateViaCookie(Request $request)
    {
        if (!$token = $this->getTokenViaCookie($request)) {
            return null;
        }

        // If this user exists, we will return this user and attach a "transient" token to
        // the user model. The transient token assumes it has all scopes since the user
        // is physically logged into the application via the application's interface.
        if ($user = $this->provider->retrieveById($token['sub'])) {
            return $user;
        }
    }

    private function getTokenViaCookie(Request $request): ?array
    {
        // If we need to retrieve the token from the cookie, it'll be encrypted so we must
        // first decrypt the cookie and then attempt to find the token value within the
        // database. If we can't decrypt the value we'll bail out with a null return.
        try {
            $token = $this->decodeJwtTokenCookie($request);
        } catch (\Exception $e) {
            return null;
        }

        // We will compare the CSRF token in the decoded API token against the CSRF header
        // sent with the request. If they don't match then this request isn't sent from
        // a valid source and we won't authenticate the request for further handling.
        if (!$this->validCsrf($token, $request) || time() >= $token['expiry']) {
            return null;
        }

        return $token;
    }

    private function validCsrf($token, Request $request): bool
    {
        return isset($token['csrf']) && hash_equals($token['csrf'], (string)$this->getTokenFromRequest($request));
    }

    private function getTokenFromRequest(Request $request)
    {
        $token = $request->header('X-CSRF-TOKEN');

        if (!$token && $header = $request->header('X-XSRF-TOKEN')) {
            $token = CookieValuePrefix::remove($this->encryptor->decrypt($header, static::serialized()));
        }

        return $token;
    }

    private function decodeJwtTokenCookie(Request $request): array
    {
        $jwt = $request->cookie(config('identity.cookie'));

        return (array)JWT::decode(
            true
                ? CookieValuePrefix::remove($this->encryptor->decrypt($jwt, false))
                : $jwt,
            new Key($this->encryptor->getKey(), 'HS256')
        );
    }

    /**
     * Determine if the cookie contents should be serialized.
     *
     * @return bool
     */
    public static function serialized()
    {
        return EncryptCookies::serialized('XSRF-TOKEN');
    }
}
