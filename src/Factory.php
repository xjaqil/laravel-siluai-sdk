<?php

namespace Aqil\SiluAi;

use Aqil\SiluAi\Kernel\ServiceContainer;

/**
 * Class Factory.
 *
 * @method static Payment\Application payment(array $config)
 * @method static OpenPlatform\Application openPlatform(array $config)
 */
class Factory
{
    /**
     * @param string $name
     * @param array $config
     *
     * @return ServiceContainer
     */
    public static function make(string $name, array $config): ServiceContainer
    {
        $namespace = Kernel\Support\Str::studly($name);
        $application = "\\Aqil\\SiluAi\\{$namespace}\\Application";

        return new $application($config);
    }

    /**
     * Dynamically pass methods to the application.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return self::make($name, ...$arguments);
    }
}
