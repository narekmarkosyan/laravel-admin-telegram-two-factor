<?php

namespace Narekmarkosyan\LaravelAdminTelegramTwoFactor;

use Encore\Admin\Extension;

class AuthTelegramTwoFactor extends Extension
{
    public static string $group = 'auth-telegram-two-factor';
    public $name = 'auth-telegram-two-factor';
    public $views = __DIR__ . '/../resources/views';
}
