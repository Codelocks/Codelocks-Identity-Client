<?php

namespace Codelocks\Identity\Middleware;

use Carbon\Carbon;
use Closure;
use Firebase\JWT\JWT;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CreateFreshApiToken
{

    private string $cookie;
    private ?string $guard;

    public function __construct()
    {
        $this->cookie    = config('identity.cookie', 'codelocks_cookie');
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @param null $guard
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $guard = null): Response
    {
        $this->guard = $guard;

        $response = $next($request);

        if ($this->shouldReceiveFreshToken($request, $response)) {
            $response->withCookie($this->makeCookie(
                $request->user($this->guard)->getAuthIdentifier(),
                $request->session()->token()
            ));
        }

        return $response;
    }

    public function makeCookie($userId, $csrfToken): Cookie
    {
        $config = config('session');

        $expiration = Carbon::now()->addMinutes($config['lifetime']);

        return new Cookie(
            $this->cookie,
            $this->createToken($userId, $csrfToken, $expiration),
            $expiration,
            $config['path'],
            $config['domain'],
            $config['secure'],
            true,
            false,
            $config['same_site'] ?? null
        );
    }

    /**
     * Create a new JWT token for the given user ID and CSRF token.
     *
     * @param mixed $userId
     * @param string $csrfToken
     * @param \Carbon\Carbon $expiration
     * @return string
     */
    private function createToken($userId, string $csrfToken, Carbon $expiration): string
    {
        return JWT::encode([
            'sub'    => $userId,
            'csrf'   => $csrfToken,
            'expiry' => $expiration->getTimestamp(),
        ], Crypt::getKey(), 'HS256');
    }

    /**
     * Determine if the given request should receive a fresh token.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @return bool
     */
    protected function shouldReceiveFreshToken($request, $response): bool
    {
        return $this->requestShouldReceiveFreshToken($request) &&
            $this->responseShouldReceiveFreshToken($response);
    }

    /**
     * Determine if the request should receive a fresh token.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function requestShouldReceiveFreshToken($request): bool
    {
        return $request->isMethod('GET') && $request->user($this->guard);
    }

    /**
     * Determine if the response should receive a fresh token.
     *
     * @param \Illuminate\Http\Response $response
     * @return bool
     */
    protected function responseShouldReceiveFreshToken($response): bool
    {
        return ($response instanceof \Illuminate\Http\Response ||
                $response instanceof JsonResponse) &&
            !$this->alreadyContainsToken($response);
    }

    /**
     * Determine if the given response already contains an API token.
     *
     * This avoids us overwriting a just "refreshed" token.
     *
     * @param \Illuminate\Http\Response $response
     * @return bool
     */
    protected function alreadyContainsToken($response): bool
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $this->cookie) {
                return true;
            }
        }

        return false;
    }
}
