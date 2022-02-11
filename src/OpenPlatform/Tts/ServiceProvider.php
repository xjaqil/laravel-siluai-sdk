<?php


namespace Aqil\SiluAi\OpenPlatform\Tts;

use Pimple\Container;
use Pimple\ServiceProviderInterface;


class ServiceProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['tts'] = function ($app) {
            return new Client($app);
        };
    }
}
