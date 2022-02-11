<?php


namespace Aqil\SiluAi\Payment\Order;

use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Aqil\SiluAi\Payment\Kernel\BaseClient;
use Aqil\SiluAi\Kernel\Exceptions\InvalidArgumentException;
use Aqil\SiluAi\Kernel\Exceptions\InvalidConfigException;

class Client extends BaseClient
{
    /**
     * Unify order.
     *
     * @param array $params
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     */
    public function unify(array $params)
    {
        $params['app_id'] = $this->app['config']['app_id'];
        $params['notify_url'] = $params['notify_url'] ?? $this->app['config']['notify_url'];


        return $this->request('create_order', $params);
    }

    /**
     * Query order by out trade number.
     *
     * @param string $number
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function queryByOutOrderNumber(string $number)
    {
        return $this->query([
            'out_order_no' => $number,
        ]);
    }


    /**
     * @param array $params
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws \Aqil\SiluAi\Kernel\Exceptions\InvalidArgumentException
     * @throws \Aqil\SiluAi\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Aqil\SiluAi\Kernel\Exceptions\InvalidConfigException
     */

    protected function query(array $params)
    {
        $params['app_id'] = $this->app['config']->app_id;

        return $this->request('query_order', $params);
    }

    /**
     * Close order by out_trade_no.
     *
     * @param array $params
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refund(array $params)
    {

        return $this->request('create_refund', $params);
    }


    /**
     * Close order by out_trade_no.
     *
     * @param string $number
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function queryRefund(string $number)
    {

        $params['app_id'] = $this->app['config']->app_id;
        $params['out_refund_no'] = $number;
        return $this->request('query_refund', $params);
    }
}
