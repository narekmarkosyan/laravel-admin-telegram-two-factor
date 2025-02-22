<?php

namespace Narekmarkosyan\LaravelAdminTelegramTwoFactor\Http\Middleware;

use App\Traits\ApiGenericResponse;
use Closure;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Narekmarkosyan\LaravelAdminTelegramTwoFactor\Helpers\TwoFactorValidationHelper;

class AuthAdminTelegramTwoFactor
{
    use ApiGenericResponse;

    public function handle(Request $request, Closure $next)
    {
        /** @var Administrator $admin */
        $admin = $request->user('admin');

        // Nothing to do, we are not logged in
        // or, we are on the 2fa stage so need to skip, or we get stuck in a loop
        if ($admin === null) {
            return $next($request);
        }

        if(!TwoFactorValidationHelper::twoFactorCompleted($admin))
        {
            abort(redirect(route(admin_get_route('auth.2fa.telegram'))));
        }

        return $next($request);
    }

}
