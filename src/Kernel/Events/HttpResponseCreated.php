<?php


namespace Aqil\SiluAi\Kernel\Events;

use Psr\Http\Message\ResponseInterface;


class HttpResponseCreated
{
    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }
}
