<?php

namespace Codelocks\Controllers;

use Codelocks\Contracts\StoreTokenUser;
use Codelocks\Socialite\CodelocksIdentityProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;

class OAuthController extends Controller
{
    public function redirect()
    {
        $config = config('identity');
        return Socialite::buildProvider(CodelocksIdentityProvider::class, $config)->redirect();
    }


    public function callback(Request $request)
    {
        if ($request->has('error')) {
            Log::error($request->get('error_description'), $request->all());
            abort(500, $request->get('error_description'));
        }
        $config = config('identity');
        /**
         * @var $authorizedUser User
         */
        $authorizedUser = Socialite::buildProvider(CodelocksIdentityProvider::class, $config)->user();
        $userInstance   = app(StoreTokenUser::class);
        /**
         * @var $user Authenticatable
         */
        $user = $userInstance->findAuthorizedUser($authorizedUser);
        $user->forceFill([
            'token'         => $authorizedUser->token,
            'refresh_token' => $authorizedUser->refreshToken,
            'expired_at'    => now()->addSeconds($authorizedUser->expiresIn),
            'scopes'        => $authorizedUser->approvedScopes
        ])->save();
        Auth::login($user, true);
        return redirect()->intended(data_get($config, 'home'));
    }
}