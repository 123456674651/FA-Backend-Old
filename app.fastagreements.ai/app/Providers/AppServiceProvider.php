<?php

namespace App\Providers;

use App\Models\Customer;
use App\Services\Auth\JwtService;
use App\Services\Auth\JwtVerificationException;
use App\Services\Payment\PaymentVerificationService;
use App\Services\Payment\RazorpayApiGateway;
use App\Services\Payment\RazorpayGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RazorpayGateway::class, fn () => new RazorpayApiGateway(
            (string) config('services.razorpay.key_id'),
            (string) config('services.razorpay.key_secret'),
        ));

        $this->app->bind(PaymentVerificationService::class, fn () => new PaymentVerificationService(
            (string) config('services.razorpay.key_secret'),
            (string) config('services.razorpay.webhook_secret'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');

        $this->registerCustomerGuard();
    }

    /**
     * Backs the `customer` guard declared in config/auth.php.
     *
     * The AuthenticateJwt middleware normally resolves and sets the user
     * itself, because it can turn each failure into a specific response code.
     * This resolver is what makes `auth('customer')` work anywhere else —
     * including on routes that are public but behave differently when someone
     * happens to be signed in. It returns null rather than throwing, since an
     * absent or bad token on such a route just means "anonymous".
     */
    private function registerCustomerGuard(): void
    {
        Auth::viaRequest('jwt-customer', function (Request $request): ?Customer {
            /** @var JwtService $jwt */
            $jwt = app(JwtService::class);

            $token = $jwt->bearerFrom($request->header('Authorization'));

            if ($token === null) {
                return null;
            }

            try {
                $claims = $jwt->verify($token);
            } catch (JwtVerificationException) {
                return null;
            }

            $customer = Customer::find((int) $claims['sub']);

            return ($customer !== null && $customer->is_active) ? $customer : null;
        });
    }
}
