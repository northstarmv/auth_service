<?php

namespace App\Providers;

use App\Models\User;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use \Illuminate\Support\Facades\Validator;
use HTMLPurifier;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        Validator::extend('xssPrevent', function ($attribute, $value, $parameters, $validator) {
            // Instantiate HTMLPurifier and sanitize the value
            
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', ''); // Disallow all HTML tags and attributes
            $purifier = new HTMLPurifier($config);
            $sanitizedValue = $purifier->purify($value);
            request()->merge([$attribute => $sanitizedValue]);
            return  true;
        });

        Validator::extend('phoneValidate', function ($attribute, $value, $parameters, $validator) {
            // Instantiate HTMLPurifier and sanitize the value
            
            $digitsOnly = preg_replace('/\D/', '', $value);

            if (strlen($digitsOnly) < 10 || strlen($digitsOnly) > 15) {
                return false;
            }
            
            // Check if the phone number is in a valid format
            $regex = '/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\/\.\/0-9]*$/';
            return preg_match($regex, $value);
        });

        Validator::replacer('phoneValidate', function ($message, $attribute, $rule, $parameters) {
            // Customize the error message here
            return 'not a valid phone number.';
        });

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                return User::where('api_token', $request->input('api_token'))->first();
            }
        });
    }
}
