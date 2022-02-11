<?php

namespace Aqil\SiluAi\OpenPlatform\Kernel;


use Aqil\SiluAi\Kernel\Exceptions\InvalidConfigException;
use Aqil\SiluAi\Kernel\Traits\HasHttpRequests;
use Aqil\SiluAi\OpenPlatform\Application;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;

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
     * Make a API request.
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @param array $options
     * @param bool $returnResponse
     *
     * @return array|\Illuminate\Support\Collection|object|ResponseInterface|string
     *
     * @throws GuzzleException|InvalidConfigException
     */
    protected function request(string $endpoint, array $params = [], $method = 'post', array $options = [], $returnResponse = false)
    {
        $base = [

        ];

        $params = array_filter(array_merge($base, $this->prepends(), $params), 'strlen');

        $options = array_merge(['form_params' => $params], $options);

        $this->pushMiddleware($this->logMiddleware(), 'log');

        $response = $this->performRequest($endpoint, $method, $options);

        return $returnResponse ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware(): \Closure
    {
        $formatter = new MessageFormatter($this->app['config']['http.log_template'] ?? MessageFormatter::DEBUG);

        return Middleware::log($this->app['logger'], $formatter);
    }

}
