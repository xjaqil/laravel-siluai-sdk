<?php

namespace Aqil\SiluAi\OpenPlatform\Tts;

use Aqil\SiluAi\Kernel\Exceptions\InvalidConfigException;
use Aqil\SiluAi\Kernel\BaseClient;
use GuzzleHttp\Exception\GuzzleException;


class Client extends BaseClient
{

    protected string $baseUri = 'https://api.xjguoyu.cn/';

    /**
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    public function apply(array $params)
    {
        return $this->httpPostJson('api/tts', $params);
    }
}
