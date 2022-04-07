<?php


namespace Aqil\SiluAi\OpenPlatform;

use Aqil\SiluAi\Kernel\ServiceContainer;


/**
 * Class Application.
 *
 * @property \Aqil\SiluAi\OpenPlatform\Tts\Client $tts
 * @property \Aqil\SiluAi\OpenPlatform\Translate\Client $translate
 * @property \Aqil\SiluAi\OpenPlatform\Speech\Client $speech
 * @property \Aqil\SiluAi\OpenPlatform\Ocr\Client $ocr
 * */
class Application extends ServiceContainer
{

    protected array $providers = [
        Base\ServiceProvider::class,
        Auth\ServiceProvider::class,
        Tts\ServiceProvider::class,
        Speech\ServiceProvider::class,
        Translate\ServiceProvider::class,
        Ocr\ServiceProvider::class

    ];

    /**
     * Handle dynamic calls.
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return $this->base->$method(...$args);
    }
}
