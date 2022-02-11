<?php


namespace Aqil\SiluAi\Payment\Kernel;

use Aqil\SiluAi\Kernel\Support;
use Aqil\SiluAi\Kernel\Traits\HasHttpRequests;
use Aqil\SiluAi\Payment\Application;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Aqil\SiluAi\Kernel\Exceptions\InvalidConfigException;
use Aqil\SiluAi\Kernel\Exceptions\InvalidArgumentException;

/**
 * Class BaseClient.
 *
 * @author overtrue <i@overtrue.me>
 */
class BaseClient
{
    use HasHttpRequests {
        request as performRequest;
    }

    /**
     * @var Application
     */
    protected Application $app;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->setHttpClient($this->app['http_client']);
    }

    /**
     * Extra request params.
     *
     * @return array
     */
    protected function prepends(): array
    {
        return [];
    }


    /**
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @param array $options
     * @param false $returnResponse
     * @return array|Collection|object|ResponseInterface|string
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    protected function request(string $endpoint, array $params = [], $method = 'post', array $options = [], $returnResponse = false)
    {
        $base = [
            'mch_id'     => $this->app['config']['mch_id'],
            'sub_mch_id' => $this->app['config']['sub_mch_id'],
            'sub_appid'  => $this->app['config']['sub_appid'],
        ];

        $params = array_filter(array_merge($base, $this->prepends(), $params), 'strlen');

        $salt = $this->app->getSalt();

        $params['sign'] = Support\generate_sign($params, $salt);

        $options = array_merge(['json' => $params], $options);

        $response = $this->performRequest($endpoint, $method, $options);

        return $returnResponse ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }


    /**
     * Make a request and return raw response.
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    protected function requestRaw(string $endpoint, array $params = [], $method = 'post', array $options = [])
    {
        /** @var ResponseInterface $response */
        $response = $this->request($endpoint, $params, $method, $options, true);

        return $response;
    }

    /**
     * Make a request and return an array.
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @param array $options
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    protected function requestArray(string $endpoint, array $params = [], $method = 'post', array $options = []): array
    {
        $response = $this->requestRaw($endpoint, $params, $method, $options);

        return $this->castResponseToType($response, 'array');
    }

    /**
     * Request with SSL.
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @param array $options
     *
     * @return array|Collection|object|ResponseInterface|string
     *
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    protected function safeRequest($endpoint, array $params, $method = 'post', array $options = [])
    {
        $options = array_merge([
            'cert'    => $this->app['config']->get('cert_path'),
            'ssl_key' => $this->app['config']->get('key_path'),
        ], $options);

        return $this->request($endpoint, $params, $method, $options);
    }

}
