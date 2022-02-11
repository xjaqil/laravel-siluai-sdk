<?php


namespace Aqil\SiluAi\OpenPlatform\Base;

use Aqil\SiluAi\OpenPlatform\Kernel\BaseClient;

class Client extends BaseClient
{

    public function speech(array $params)
    {
        return $this->request('api/tts', $params);
    }

}
