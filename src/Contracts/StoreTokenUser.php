<?php

namespace Codelocks\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Two\User;

interface StoreTokenUser
{
    public function findAuthorizedUser(User $user): Authenticatable;
}