<?php

namespace TCEMT\Providers;

use Blade;
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
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \TCEMT\Http\Middleware\Secorphp::class,
        ]);

        // blade directives
        Blade::directive('recurso', function($expression) {
            $recurso = str_replace(['(',')',' ',"'",'"'], '', $expression);
            return "<?php echo (\\TCEMT\\Helpers\\SecorphpStatic::allow('$recurso') ? '' : 'disabled=\"disabled\"'); ?>";
        });

        Blade::directive('if_recurso', function($expression) {
            $recurso = str_replace(['(',')',' ',"'",'"'], '', $expression);
            return "<?php if(\\TCEMT\\Helpers\\SecorphpStatic::allow('$recurso')) : ?>";
        });

        Blade::directive('endif_recurso', function() {
            return "<?php endif; ?>";
        });

        Blade::directive('acao', function($expression) {
            list($recurso, $acao) = explode(',',str_replace(['(',')',' ',"'",'"'], '', $expression));
            return "<?php echo (\\TCEMT\\Helpers\\SecorphpStatic::allow('$recurso','$acao') ? '' : 'disabled=\"disabled\"'); ?>";
        });
        Blade::directive('if_acao', function($expression) {
            list($recurso, $acao) = explode(',',str_replace(['(',')',' ',"'",'"'], '', $expression));
            return "<?php if(\\TCEMT\\Helpers\\SecorphpStatic::allow('$recurso','$acao')) : ?>";
        });

        Blade::directive('endif_acao', function() {
            return "<?php endif; ?>";
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
