<?php

namespace Aqil\SiluAi\Kernel;

use Aqil\SiluAi\Kernel\Contracts\AccessTokenInterface;
use Aqil\SiluAi\Kernel\Exceptions\InvalidConfigException;
use Aqil\SiluAi\Kernel\Http\Response;
use Aqil\SiluAi\Kernel\Traits\HasHttpRequests;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

class BaseClient
{
    use HasHttpRequests {
        request as performRequest;
    }

    /**
     * @var ServiceContainer
     */
    protected ServiceContainer $app;

    /**
     * @var AccessTokenInterface|null
     */
    protected $accessToken = null;
    /**
     * @var string
     */
    protected string $baseUri = '';


    /**
     * BaseClient constructor.
     *
     * @param ServiceContainer $app
     * @param AccessTokenInterface|null $accessToken
     */
    public function __construct(ServiceContainer $app, AccessTokenInterface $accessToken = null)
    {
        $this->app = $app;
        $this->accessToken = $accessToken ?? $this->app['access_token'];
    }

    /**
     * GET request.
     *
     * @param string $url
     * @param array $query
     *
     * @return array|Collection|object|ResponseInterface|string
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function httpGet(string $url, array $query = [])
    {
        return $this->request($url, 'GET', ['query' => $query]);
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array $data
     *
     * @return array|Collection|object|ResponseInterface|string
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function httpPost(string $url, array $data = [])
    {
        return $this->request($url, 'POST', ['form_params' => $data]);
    }

    /**
     * JSON request.
     *
     * @param string $url
     * @param array $data
     * @param array $query
     *
     * @return array|Collection|object|ResponseInterface|string
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function httpPostJson(string $url, array $data = [], array $query = [])
    {
        return $this->request($url, 'POST', ['query' => $query, 'json' => $data]);
    }

    /**
     * Upload file.
     *
     * @param string $url
     * @param array $files
     * @param array $form
     * @param array $query
     *
     * @return array|Collection|object|ResponseInterface|string
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function httpUpload(string $url, array $files = [], array $form = [], array $query = [])
    {
        $multipart = [];
        $headers = [];

        if (isset($form['filename'])) {
            $headers = [
                'Content-Disposition' => 'form-data; name="media"; filename="' . $form['filename'] . '"'
            ];
        }

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
                'headers' => $headers
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        return $this->request(
            $url,
            'POST',
            ['query' => $query, 'multipart' => $multipart, 'connect_timeout' => 30, 'timeout' => 30, 'read_timeout' => 30]
        );
    }


    /**
     * @return AccessTokenInterface
     */
    public function getAccessToken(): AccessTokenInterface
    {
        return $this->accessToken;
    }

    /**
     * @param AccessTokenInterface $accessToken
     *
     * @return $this
     */
    public function setAccessToken(AccessTokenInterface $accessToken): BaseClient
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $options
     * @param bool $returnRaw
     *
     * @return array|Collection|object|ResponseInterface|string
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function request(string $url, string $method = 'GET', array $options = [], $returnRaw = false)
    {

        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        $response = $this->performRequest($url, $method, $options);

        $this->app->events->dispatch(new Events\HttpResponseCreated($response));


        return $returnRaw ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $options
     *
     * @return Response
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function requestRaw(string $url, string $method = 'GET', array $options = []): Response
    {
        return Response::buildFromPsrResponse($this->request($url, $method, $options, true));
    }


    /**
     * @return void
     */
    protected function registerHttpMiddlewares()
    {
        // retry
        $this->pushMiddleware($this->retryMiddleware(), 'retry');
        // access token
        $this->pushMiddleware($this->accessTokenMiddleware(), 'access_token');
        // log
        $this->pushMiddleware($this->logMiddleware(), 'log');
    }


    /**
     * @return \Closure
     */
    protected function accessTokenMiddleware(): \Closure
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if ($this->accessToken) {
                    $request = $this->accessToken->applyToRequest($request, $options);
                }

                return $handler($request, $options);
            };
        };
    }


    /**
     * @return callable
     */
    protected function logMiddleware(): callable
    {
        $formatter = new MessageFormatter($this->app['config']['http.log_template'] ?? MessageFormatter::DEBUG);

        return Middleware::log($this->app['logger'], $formatter, LogLevel::DEBUG);
    }


    /**
     * @return callable
     */
    protected function retryMiddleware(): callable
    {
        return Middleware::retry(
            function (
                $retries,
                RequestInterface $request,
                ResponseInterface $response = null
            ) {
                // Limit the number of retries to 2
                if ($retries < $this->app->config->get('http.max_retries', 1) && $response && $body = $response->getBody()) {
                    // Retry on server errors
                    $response = json_decode($body, true);

                    if (!empty($response['errcode']) && in_array(abs($response['errcode']), [40001, 40014, 42001], true)) {
                        $this->accessToken->refresh();
                        $this->app['logger']->debug('Retrying with refreshed access token.');

                        return true;
                    }
                }

                return false;
            },
            function () {
                return abs($this->app->config->get('http.retry_delay', 500));
            }
        );
    }

}
