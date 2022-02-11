<?php


namespace Aqil\SiluAi\OpenPlatform\Auth;

use Aqil\SiluAi\Kernel\BaseClient;
use Aqil\SiluAi\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;


class Client extends BaseClient
{
    /**
     * Get session info by code.
     *
     * @param string $code
     *
     * @return array|Collection|object|ResponseInterface|string
     *
     * @throws InvalidConfigException|GuzzleException
     */
    public function session(string $code)
    {
        $params = [
            'appid' => $this->app['config']['app_id'],
            'secret' => $this->app['config']['secret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];

        return $this->httpGet('sns/jscode2session', $params);
    }
}
