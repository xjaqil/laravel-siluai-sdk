<?php


namespace Aqil\SiluAi\Kernel\Events;

use Symfony\Component\HttpFoundation\Response;


class ServerGuardResponseCreated
{
    /**
     * @var Response
     */
    public $response;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }
}
