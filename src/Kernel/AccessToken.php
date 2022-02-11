<?php


namespace Aqil\SiluAi\Kernel;

use Aqil\SiluAi\Kernel\Contracts\AccessTokenInterface;
use Aqil\SiluAi\Kernel\Exceptions\HttpException;
use Aqil\SiluAi\Kernel\Exceptions\InvalidArgumentException;
use Aqil\SiluAi\Kernel\Exceptions\InvalidConfigException;
use Aqil\SiluAi\Kernel\Exceptions\RuntimeException;
use Aqil\SiluAi\Kernel\Support\Collection;
use Aqil\SiluAi\Kernel\Traits\HasHttpRequests;
use Aqil\SiluAi\Kernel\Traits\InteractsWithCache;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


abstract class AccessToken implements AccessTokenInterface
{
    use HasHttpRequests;
    use InteractsWithCache;

    /**
     * @var ServiceContainer
     */
    protected ServiceContainer $app;

    /**
     * @var string
     */
    protected string $requestMethod = 'GET';

    /**
     * @var string
     */
    protected string $endpointToGetToken;

    /**
     * @var string
     */
    protected string $queryName;

    /**
     * @var array
     */
    protected array $token;

    /**
     * @var string
     */
    protected string $tokenKey = 'access_token';

    /**
     * @var string
     */
    protected string $cachePrefix = 'siluai.kernel.access_token.';

    /**
     * AccessToken constructor.
     *
     * @param ServiceContainer $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }

    /**
     * @return array
     *
     * @throws HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function getRefreshedToken(): array
    {
        return $this->getToken(true);
    }

    /**
     * @param bool $refresh
     *
     * @return array
     *
     * @throws HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function getToken(bool $refresh = false): array
    {
        $cacheKey = $this->getCacheKey();
        $cache = $this->getCache();

        if (!$refresh && $cache->has($cacheKey) && $result = $cache->get($cacheKey)) {
            return $result;
        }

        $token = $this->requestToken($this->getCredentials(), true);

        $this->setToken($token[$this->tokenKey], $token['expires_in'] ?? 7200);

        $this->app->events->dispatch(new Events\AccessTokenRefreshed($this));

        return $token;
    }

    /**
     * @param string $token
     * @param int $lifetime
     *
     * @return AccessTokenInterface
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setToken(string $token, int $lifetime = 7200): AccessTokenInterface
    {
        $this->getCache()->set($this->getCacheKey(), [
            $this->tokenKey => $token,
            'expires_in' => $lifetime,
        ], $lifetime);

        if (!$this->getCache()->has($this->getCacheKey())) {
            throw new RuntimeException('Failed to cache access token.');
        }

        return $this;
    }

    /**
     * @return AccessTokenInterface
     *
     * @throws HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function refresh(): AccessTokenInterface
    {
        $this->getToken(true);

        return $this;
    }

    /**
     * @param array $credentials
     * @param bool $toArray
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    public function requestToken(array $credentials, $toArray = false)
    {
        $response = $this->sendRequest($credentials);
        $result = json_decode($response->getBody()->getContents(), true);
        $formatted = $this->castResponseToType($response, $this->app['config']->get('response_type'));

        if (empty($result[$this->tokenKey])) {
            throw new HttpException('Request access_token fail: ' . json_encode($result, JSON_UNESCAPED_UNICODE), $response, $formatted);
        }

        return $toArray ? $result : $formatted;
    }

    /**
     * @param RequestInterface $request
     * @param array $requestOptions
     *
     * @return RequestInterface
     *
     * @throws HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface
    {

        return $request->withHeader('Authorization', 'Bearer ' . $this->getToken()[$this->tokenKey])
            ->withUri($request->getUri());
    }

    /**
     * Send http request.
     *
     * @param array $credentials
     *
     * @return ResponseInterface
     *
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    protected function sendRequest(array $credentials): ResponseInterface
    {
        $options = [
            'query' => [
                'grant_type' => $credentials['grant_type']
            ],
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($credentials['client_id'] . ':' . $credentials['secret'])
            ]
        ];

        return $this->setHttpClient($this->app['http_client'])->request($this->getEndpoint(), $this->requestMethod, $options);
    }

    /**
     * @return string
     */
    protected function getCacheKey(): string
    {
        return $this->cachePrefix . md5(json_encode($this->getCredentials()));
    }

    /**
     * The request query will be used to add to the request.
     *
     * @return array
     *
     * @throws HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function getQuery(): array
    {
        return [$this->queryName ?? $this->tokenKey => $this->getToken()[$this->tokenKey]];
    }


    /**
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function getEndpoint(): string
    {
        if (empty($this->endpointToGetToken)) {
            throw new InvalidArgumentException('No endpoint for access token request.');
        }

        return $this->endpointToGetToken;
    }

    /**
     * @return string
     */
    public function getTokenKey(): string
    {
        return $this->tokenKey;
    }

    /**
     * Credential for get token.
     *
     * @return array
     */
    abstract protected function getCredentials(): array;
}
