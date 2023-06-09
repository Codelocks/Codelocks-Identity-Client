<?php

namespace Codelocks\Identity\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface StoreTokenUser
{
    public function findAuthorizedUser($user): Authenticatable;
}
