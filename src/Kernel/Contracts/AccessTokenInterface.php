<?php


namespace Aqil\SiluAi\Kernel\Contracts;

use Psr\Http\Message\RequestInterface;


interface AccessTokenInterface
{
    /**
     * @return array
     */
    public function getToken(): array;

    /**
     * @return AccessTokenInterface
     */
    public function refresh(): self;

    /**
     * @param RequestInterface $request
     * @param array $requestOptions
     *
     * @return RequestInterface
     */
    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface;
}
