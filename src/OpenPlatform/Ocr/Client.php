<?php

namespace Aqil\SiluAi\OpenPlatform\Ocr;

use Aqil\SiluAi\Kernel\Exceptions\InvalidConfigException;
use Aqil\SiluAi\Kernel\BaseClient;
use GuzzleHttp\Exception\GuzzleException;


class Client extends BaseClient
{

    protected string $baseUri = 'https://api.xjslwt.com/';

    /**
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    public function apply(array $params)
    {
        return $this->httpPostJson('api/ocr', $params);
    }
}
