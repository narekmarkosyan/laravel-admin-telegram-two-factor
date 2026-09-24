Telegram two-factor authentication for laravel-admin
======
Inspired from https://github.com/shanerutter/laravel-admin-email-two-factor

Adds two-factor authentication to admin login, admin will be sent a message in Telegram with a 6-digit code to complete sign in.

### Notes
A new "telegram_id" field is added to the database as laravel-admin does not have this by default.
Once installed and migrations completed, you will need to set each user's telegram ID via the database.
No way of doing it via UI has been implemented at this time.

### Installation

Requires PHP 8.3 or newer and Laravel 11, 12, or 13. The package uses
[`zen-geeks/laravel-admin`](https://github.com/zen-geeks/laravel-admin), a
Laravel 11–13 compatible fork that retains the `Encore\Admin` namespace.
If your application currently requires `encore/laravel-admin`, replace that
dependency with `zen-geeks/laravel-admin` before installing this package.

Laravel 11 is past its security support window and currently has advisories
that may cause Composer to block installation. The Laravel 11 CI job disables
advisory blocking only to verify API compatibility. Use Laravel 12 or 13 for
deployments.

```
composer require narekmarkosyan/laravel-admin-telegram-two-factor
```

### Migration
Add telegram_id field to admin users table.
```
php artisan migrate
```

### Configuration

In the extensions section of the `config/admin.php` file, add configurations
```
'extensions' => [
    'auth-telegram-two-factor' => [
        'enable' => (bool)env('ADMIN_AUTH_TELEGRAM_TWO_FACTOR', true),
        'botKey' => env('ADMIN_AUTH_TELEGRAM_TWO_FACTOR_BOT_KEY'),
        'pinLength' => (int)env('ADMIN_AUTH_TELEGRAM_TWO_FACTOR_PIN_LENGTH', 6),
        'rememberDays' => (int)env('ADMIN_AUTH_TELEGRAM_TWO_FACTOR_REMEMBER_DAYS', 1),
    ]
]
```

In the `.env` file, add configurations
```
ADMIN_AUTH_TELEGRAM_TWO_FACTOR=true
ADMIN_AUTH_TELEGRAM_TWO_FACTOR_BOT_KEY=123456789:AABBB_ZXCVBNMdfghjXCVvb_AA
ADMIN_AUTH_TELEGRAM_TWO_FACTOR_PIN_LENGTH=6
ADMIN_AUTH_TELEGRAM_TWO_FACTOR_REMEMBER_DAYS=1
```

### License

Licensed under [The MIT License (MIT)](LICENSE).
