<?php

namespace App\Providers;

use App\Models\User;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // configuracion de scramble
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer', 'JWT')
                );
            });

        Gate::define('viewApiDocs', function (User $user) {
            // return in_array($user->email, ['admin@app.com']);
            // aqui puedes definir quien puede ver la documentacion de la api
            return true;
        });

        Gate::before(function ($user, $ability) {
            return $user->hasRole('catalogador') ? true : null;
        });
    }
}