<?php

namespace Codelocks\Identity\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Two\User;

/**
 * @extends Authenticatable
 * @extends \Illuminate\Database\Eloquent\Model
 */
trait TokenUser
{
    public function findAuthorizedUser(User $authUser): Authenticatable
    {
        $user = $this->where('oauth_provider_id', $authUser->id)
            ->where('oauth_provider', config('identity.provider_name'))
            ->first();
        if (!$user)
            $user = new static();
        $user->forceFill([
            'oauth_provider_id' => $authUser->id,
            'oauth_provider'    => config('identity.provider_name'),
            'name'              => $authUser->name,
            'email'             => $authUser->email,
            'avatar'            => $authUser->avatar,
            'orggid'            => data_get($authUser, 'orggid'),
        ]);
        return $user;
    }
}