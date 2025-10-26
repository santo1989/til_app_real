<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
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
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        // Register a runtime alias for the role middleware so routes can use ->middleware('role:...')
        $router->aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);

        // Blade directive to render a human-friendly rating label from a DB token.
        // Usage in Blade: @ratingLabel($appraisal->rating)
        Blade::directive('ratingLabel', function ($expression) {
            return "<?php echo \\App\\Support\\Rating::toDisplayLabel($expression); ?>";
        });
    }
}
