<?php

namespace TCEMT\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SecorphpServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Run service provider boot operations.
     *
     * @return void
     */
    public function boot() {

        $secorphp = __DIR__ . '/../Config/secorphp.php';

        // Add publishable configuration.
        $this->publishes([
            $secorphp => config_path('secorphp.php'),
        ], 'secorphp');

        // Register Middleware
        $this->app['router']->middlewareGroup('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \TCEMT\Http\Middleware\Secorphp::class,
        ]);

        // blade directives
        Blade::directive('recurso', function($expression) {
            $recurso = str_replace(['(',')',' ',"'",'"'], '', $expression);
            return "<?php echo (\\TCEMT\\Helpers\\SecorphpStatic::allow('$recurso') ? '' : 'disabled=\"disabled\"'); ?>";
        });

        Blade::if('if_recurso', function(string $recurso) {
            return \TCEMT\Helpers\SecorphpStatic::allow($recurso);
        });

        Blade::directive('acao', function($expression) {
            list($recurso, $acao) = explode(',',str_replace(['(',')',' ',"'",'"'], '', $expression));
            return "<?php echo (\\TCEMT\\Helpers\\SecorphpStatic::allow('$recurso','$acao') ? '' : 'disabled=\"disabled\"'); ?>";
        });

        Blade::if('if_acao', function(string $recurso, string $acao) {
            return \TCEMT\Helpers\SecorphpStatic::allow($recurso, $acao);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('secorphp', function ($app) {
            return new \TCEMT\Helpers\Secorphp();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return ['secorphp'];
    }
}
