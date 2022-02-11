<?php


namespace Aqil\SiluAi;

use Aqil\SiluAi\OpenPlatform\Application as Tts;
use Aqil\SiluAi\Payment\Application as Payment;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;


class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Boot the provider.
     */
    public function boot()
    {
    }

    /**
     * Setup the config.
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/config.php');

        $this->publishes([$source => config_path('siluai.php')], 'siluai');

        $this->mergeConfigFrom($source, 'siluai');
    }

    /**
     * Register the provider.
     */
    public function register()
    {
        $this->setupConfig();

        $apps = [
            'payment' => Payment::class,
            'tts' => Tts::class,
        ];

        foreach ($apps as $name => $class) {
            if (empty(config('siluai.' . $name))) {
                continue;
            }


            if (!empty(config('siluai.' . $name . '.app_id'))) {
                $accounts = [
                    'default' => config('siluai.' . $name),
                ];
                config(['siluai.' . $name . '.default' => $accounts['default']]);
            } else {
                $accounts = config('siluai.' . $name);
            }

            foreach ($accounts as $account => $config) {
                $this->app->singleton("siluai.{$name}.{$account}", function ($laravelApp) use ($name, $account, $config, $class) {
                    $app = new $class(array_merge(config('siluai.defaults', []), $config));
                    if (config('siluai.defaults.use_laravel_cache')) {
                        $app['cache'] = $laravelApp['cache.store'];
                    }
                    $app['request'] = $laravelApp['request'];

                    return $app;
                });
            }
            $this->app->alias("siluai.{$name}.default", 'siluai.' . $name);

            $this->app->alias('siluai.' . $name, $class);
        }
    }

}
