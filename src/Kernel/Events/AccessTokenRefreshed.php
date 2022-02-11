<?php


namespace Aqil\SiluAi\Kernel\Events;

use Aqil\SiluAi\Kernel\AccessToken;


class AccessTokenRefreshed
{
    /**
     * @var AccessToken
     */
    public $accessToken;

    /**
     * @param AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }
}
