<?php


namespace Aqil\SiluAi\OpenPlatform\Translate;

use Pimple\Container;
use Pimple\ServiceProviderInterface;


class ServiceProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['translate'] = function ($app) {
            return new Client($app);
        };
    }
}
