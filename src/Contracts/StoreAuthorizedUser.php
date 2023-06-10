<?php

namespace Codelocks\Identity\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Authenticatable
 * @extends \Illuminate\Database\Eloquent\Model
 */
trait StoreAuthorizedUser
{
    public function findAuthorizedUser($authUser): Authenticatable
    {
        $user = $this->where('oauth_provider_id', data_get($authUser, 'id'))
            ->where('oauth_provider', config('identity.provider_name'))
            ->first();
        if (!$user) {
            $user = new static();
            $user->forceFill([
                'oauth_provider_id' => data_get($authUser, 'id'),
                'oauth_provider'    => config('identity.provider_name'),
                'name'              => data_get($authUser, 'name'),
                'email'             => data_get($authUser, 'email'),
                'avatar'            => data_get($authUser, 'avatar'),
                'orggid'            => data_get($authUser, 'orggid'),
                'password'          => Hash::make(Str::password()),
            ]);
        }
        return $user;
    }

    public function retrieveById($sub): Authenticatable
    {
        return $this->where('oauth_provider_id', $sub)
            ->where('oauth_provider', config('identity.provider_name'))
            ->first();
    }
}
