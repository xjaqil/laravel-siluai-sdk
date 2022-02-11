<?php


namespace Aqil\SiluAi\Kernel;


use Aqil\SiluAi\Kernel\Providers\EventDispatcherServiceProvider;
use Aqil\SiluAi\Kernel\Providers\LogServiceProvider;
use GuzzleHttp\Client;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Aqil\SiluAi\Kernel\Providers\ConfigServiceProvider;
use Aqil\SiluAi\Kernel\Providers\HttpClientServiceProvider;
use Aqil\SiluAi\Kernel\Providers\RequestServiceProvider;

/**
 *
 * @property Config $config
 * @property Request $request
 * @property Client $http_client
 * @property Logger $logger
 * @property EventDispatcher $events
 */
class ServiceContainer extends Container
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected array $providers = [];


    /**
     * @var array
     */
    protected array $defaultConfig = [];

    /**
     * @var array
     */
    protected array $userConfig = [];

    /**
     * Constructor.
     *
     * @param array $config
     * @param array $prepends
     * @param string|null $id
     */
    public function __construct(array $config = [], array $prepends = [], string $id = null)
    {
        $this->userConfig = $config;

        parent::__construct($prepends);

        $this->registerProviders($this->getProviders());

        $this->id = $id;


        $this->events->dispatch(new Events\ApplicationInitialized($this));
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id ?? $this->id = md5(json_encode($this->userConfig));
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $base = [
            'http' => [
                'timeout' => 2.0,
                'base_uri' => 'https://api.xjslwt.com/',
            ],
        ];

        return array_replace_recursive($base, $this->defaultConfig, $this->userConfig);
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders(): array
    {
        return array_merge([
            ConfigServiceProvider::class,
            LogServiceProvider::class,
            RequestServiceProvider::class,
            HttpClientServiceProvider::class,
            EventDispatcherServiceProvider::class,
        ], $this->providers);
    }

    /**
     * @param string $id
     * @param mixed $value
     */
    public function rebind(string $id, $value)
    {
        $this->offsetUnset($id);
        $this->offsetSet($id, $value);
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get(string $id)
    {

        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * @param array $providers
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            parent::register(new $provider());
        }
    }
}
