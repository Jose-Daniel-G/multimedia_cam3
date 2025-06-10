composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
php artisan make:controller AuthController
git config --get remote.origin.url

## Each time install backend execute
php artisan jwt:secret