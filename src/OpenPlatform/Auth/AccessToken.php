<?php


namespace Aqil\SiluAi\OpenPlatform\Auth;

use Aqil\SiluAi\Kernel\AccessToken as BaseAccessToken;


class AccessToken extends BaseAccessToken
{
    /**
     * @var string
     */
    protected string $endpointToGetToken = '/auth/oauth/token';


    protected string $requestMethod = 'POST';

    /**
     * {@inheritdoc}
     */
    protected function getCredentials(): array
    {
        return [
            'grant_type' => 'client_credentials',
            'client_id' => $this->app['config']['client_id'],
            'secret' => $this->app['config']['secret'],
        ];
    }
}
