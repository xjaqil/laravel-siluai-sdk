<?php

namespace Aqil\SiluAi\Payment\Notify;

use Closure;
use Aqil\SiluAi\Kernel\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;
use Aqil\SiluAi\Payment\Kernel\Exceptions\InvalidSignException;

class Paid extends Handler
{
    /**
     * @param Closure $closure
     * @return Response
     * @throws Exception
     * @throws InvalidSignException
     */
    public function handle(Closure $closure): Response
    {
        $this->strict(
            \call_user_func($closure, $this->getMessage(), [$this, 'fail'])
        );

        return $this->toResponse();
    }
}
