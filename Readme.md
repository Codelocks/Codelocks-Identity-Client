# Codelocks Identity Client 

## install 
```shell
composer require <repo>
php artisan vender:publish --provider Codelocks\Identity\ClientServiceProvider

```
## migrate users table
```shell
php artisan migrate
```

## update user model
```php
namespace App\Models;
use Codelocks\Identity\Contracts\StoreTokenUser;
use Codelocks\Identity\Contracts\StoreAuthorizedUser;

class User extends Authenticatable implements StoreTokenUser
{
    use HasApiTokens, HasFactory, Notifiable, StoreAuthorizedUser;
}

```
## register
```php

use App\Models\User;

public function boot(): void
{
   ClientServiceProvider::registerRoutes();
   ClientServiceProvider::registerUserModel(User::class);
   ClientServiceProvider::registerGuardDriver('identity-token');
}
```

## update auth guard

```php
return [
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        // add api guard
        'api' => [
            'driver'=>'identity-token'
        ]
    ],
]
```
## update routes
```shell
Route::middleware('auth:api')->group(function () {

  //... roues
});

}
```