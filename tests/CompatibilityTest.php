<?php

namespace Tests;

use Encore\Admin\AdminServiceProvider;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ViewErrorBag;
use Narekmarkosyan\LaravelAdminTelegramTwoFactor\AuthAttemptsServiceProvider;
use Narekmarkosyan\LaravelAdminTelegramTwoFactor\Helpers\TwoFactorValidationHelper;
use Narekmarkosyan\LaravelAdminTelegramTwoFactor\Http\Middleware\AuthAdminTelegramTwoFactor;
use Orchestra\Testbench\TestCase;

class CompatibilityTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [AdminServiceProvider::class, AuthAttemptsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('admin', require __DIR__ . '/../vendor/zen-geeks/laravel-admin/config/admin.php');
        $app['config']->set('admin.extensions.auth-telegram-two-factor', [
            'enable' => true,
            'pinLength' => 6,
        ]);
        $app['config']->set('auth.guards.admin', [
            'driver' => 'session',
            'provider' => 'admin',
        ]);
        $app['config']->set('auth.providers.admin', [
            'driver' => 'eloquent',
            'model' => Administrator::class,
        ]);
    }

    public function test_routes_are_named_and_registered_for_admin_prefix(): void
    {
        $get = Route::getRoutes()->getByName('admin.auth.2fa.telegram');
        $post = Route::getRoutes()->getByName('admin.auth.2fa.telegram.verify');
        $resend = Route::getRoutes()->getByName('admin.auth.2fa.telegram.resend');

        $this->assertNotNull($get);
        $this->assertSame('admin/auth/2fa', $get->uri());
        $this->assertSame(['GET', 'HEAD'], $get->methods());
        $this->assertSame(['POST'], $post->methods());
        $this->assertSame('admin/auth/2fa/resend', $resend->uri());
        $this->assertContains('admin.auth.2fa.telegram', $this->app['router']->getMiddlewareGroups()['admin']);
        $this->assertContains('admin.auth.2fa.telegram', $get->excludedMiddleware());
        $this->assertContains('admin.auth.2fa.telegram', $post->excludedMiddleware());
        $this->assertContains('admin.auth.2fa.telegram', $resend->excludedMiddleware());
    }

    public function test_code_can_be_completed_and_remains_bound_to_admin(): void
    {
        $admin = new Administrator();
        $admin->id = 42;

        Session::forget('2fa');
        $code = TwoFactorValidationHelper::twoFactorGenerateCode($admin);

        $this->assertSame(42, Session::get('2fa.id'));
        $this->assertFalse(TwoFactorValidationHelper::twoFactorCompleted($admin));
        $this->assertTrue(TwoFactorValidationHelper::twoFactorValidateCode($admin, $code));
        $this->assertTrue(TwoFactorValidationHelper::twoFactorCompleted($admin));
        $this->assertSame(42, Session::get('2fa.id'));
    }

    public function test_another_admin_gets_a_new_challenge(): void
    {
        $first = new Administrator();
        $first->id = 42;
        $second = new Administrator();
        $second->id = 43;

        TwoFactorValidationHelper::twoFactorGenerateCode($first);

        $this->assertFalse(TwoFactorValidationHelper::twoFactorCompleted($second));
        $this->assertSame(43, Session::get('2fa.id'));
    }

    public function test_expired_code_logs_out_the_admin_guard(): void
    {
        $admin = new Administrator();
        $admin->id = 42;
        $this->app['auth']->guard('admin')->setUser($admin);
        $code = TwoFactorValidationHelper::twoFactorGenerateCode($admin);
        Session::put('2fa.expired_at', now()->subMinute());

        $this->assertFalse(TwoFactorValidationHelper::twoFactorValidateCode($admin, $code));
        $this->assertFalse($this->app['auth']->guard('admin')->check());
        $this->assertNull(Session::get('2fa'));
    }

    public function test_middleware_redirects_to_the_challenge(): void
    {
        $admin = new Administrator();
        $admin->id = 42;
        $request = Request::create('/admin');
        $request->setUserResolver(fn () => $admin);

        Session::forget('2fa');
        $response = (new AuthAdminTelegramTwoFactor())->handle($request, fn () => response('passed'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('admin.auth.2fa.telegram'), $response->headers->get('Location'));
    }

    public function test_challenge_page_renders_for_authenticated_admin(): void
    {
        $admin = new Administrator();
        $admin->id = 42;

        $this->withoutMiddleware();
        $this->actingAs($admin, 'admin');
        $this->app['view']->share('errors', new ViewErrorBag());
        $this->get('/admin/auth/2fa')
            ->assertOk()
            ->assertSee('Two Factor');
    }

    public function test_migration_can_be_rolled_back(): void
    {
        Schema::create('admin_users', function ($table) {
            $table->increments('id');
            $table->string('username');
        });

        require_once __DIR__ . '/../database/migrations/2023_05_05_163751_add_administrator_telegram_field_table.php';
        $migration = new \AddAdministratorTelegramFieldTable();
        $migration->up();

        $this->assertTrue(Schema::hasColumn('admin_users', 'telegram_id'));

        $migration->down();

        $this->assertFalse(Schema::hasColumn('admin_users', 'telegram_id'));
    }
}
