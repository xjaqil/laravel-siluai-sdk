<?php


namespace Aqil\SiluAi\Kernel\Events;

use Aqil\SiluAi\Kernel\ServiceContainer;


class ApplicationInitialized
{
    /**
     * @var ServiceContainer
     */
    public ServiceContainer $app;

    /**
     * @param ServiceContainer $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }
}
