<?php


namespace Aqil\SiluAi\OpenPlatform\Ocr;

use Pimple\Container;
use Pimple\ServiceProviderInterface;


class ServiceProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['ocr'] = function ($app) {
            return new Client($app);
        };
    }
}
