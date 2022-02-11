<?php

namespace Aqil\SiluAi\Payment;


use Closure;
use Aqil\SiluAi\Kernel\Exceptions\Exception;
use Aqil\SiluAi\Kernel\Exceptions\InvalidArgumentException;
use Aqil\SiluAi\Kernel\ServiceContainer;
use Aqil\SiluAi\Payment\Order\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Application.
 *
 * @property Client $order
 *
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected array $providers = [
        Order\ServiceProvider::class,
    ];

    /**
     * @var array
     */
    protected array $defaultConfig = [
        'http' => [
            'base_uri' => 'https://developer.toutiao.com/api/apps/ecpay/v1/',
        ],
    ];


    /**
     * @param Closure $closure
     * @return Response
     * @throws Exception
     * @throws Kernel\Exceptions\InvalidSignException
     * @codeCoverageIgnore
     *
     */
    public function handlePaidNotify(Closure $closure): Response
    {
        return (new Notify\Paid($this))->handle($closure);
    }

    /**
     * @param Closure $closure
     * @return Response
     * @throws Exception
     * @codeCoverageIgnore
     *
     */
    public function handleRefundedNotify(Closure $closure): Response
    {
        return (new Notify\Refunded($this))->handle($closure);
    }

    /**
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function getSalt(): string
    {

        $salt = $this['config']->salt;

        if (empty($salt)) {
            throw new InvalidArgumentException('config salt should not empty.');
        }

        return $salt;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this['base'], $name], $arguments);
    }
}
