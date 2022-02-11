<?php


namespace Aqil\SiluAi;

use Aqil\SiluAi\OpenPlatform\Application;
use Illuminate\Support\Facades\Facade as LaravelFacade;


class Facade extends LaravelFacade
{
    /**
     * 默认为 Server.
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return 'siluai';
    }


    /**
     * @param string $name
     * @return Application
     */
    public static function tts(string $name = ''): Application
    {
        return $name ? app('siluai.open_platform.' . $name) : app('siluai.open_platform');
    }
}
