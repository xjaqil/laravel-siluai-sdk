<?php


namespace Aqil\SiluAi\Payment\Notify;

use Closure;
use Aqil\SiluAi\Payment\Application;
use Aqil\SiluAi\Kernel\Exceptions\Exception;
use Aqil\SiluAi\Kernel\Support;
use Aqil\SiluAi\Payment\Kernel\Exceptions\InvalidSignException;
use Hash;
use Symfony\Component\HttpFoundation\Response;

abstract class Handler
{
    public const SUCCESS = 'success';
    public const FAIL = 'fail';

    /**
     * @var Application
     */
    protected Application $app;

    /**
     * @var array
     */
    protected array $message;


    /**
     * @var string|null
     */
    protected ?string $fail;

    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * Check sign.
     * If failed, throws an exception.
     *
     * @var bool
     */
    protected bool $check = true;

    /**
     * Respond with sign.
     *
     * @var bool
     */
    protected bool $sign = false;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle incoming notify.
     *
     * @param \Closure $closure
     *
     * @return Response
     */
    abstract public function handle(Closure $closure): Response;

    /**
     * @param string $message
     */
    public function fail(string $message)
    {
        $this->fail = $message;
    }

    /**
     * @param array $attributes
     * @param bool $sign
     *
     * @return $this
     */
    public function respondWith(array $attributes, bool $sign = false): Handler
    {
        $this->attributes = $attributes;
        $this->sign = $sign;

        return $this;
    }


    /**
     * @return Response
     */
    public function toResponse(): Response
    {
        $base = [
            'err_no'   => isset($this->fail) ? 1 : 0,
            'err_tips' => isset($this->fail) ? static::FAIL : static::SUCCESS,
        ];

        $attributes = array_merge($base, $this->attributes);


        return new Response(json_encode($attributes));
    }

    /**
     * Return the notify message from request.
     *
     * @return array
     *
     * @throws Exception|InvalidSignException
     */
    public function getMessage(): array
    {
        if (!empty($this->message)) {
            return $this->message;
        }

        $message = json_decode($this->app['request']->getContent(), true);


        if (!is_array($message) || empty($message)) {
            throw new Exception('Invalid request .', 400);
        }

        if ($this->check) {
            $this->validate($message);
        }

        return $this->message = json_decode($message['msg'], true);

    }

    /**
     * Decrypt message.
     *
     * @param string $key
     *
     * @return string|null
     *
     * @throws Exception
     * @throws InvalidSignException
     */
    public function decryptMessage(string $key): ?string
    {
        $message = $this->getMessage();
        if (empty($message[$key])) {
            return null;
        }

        return Support\AES::decrypt(
            base64_decode($message[$key], true),
            md5($this->app['config']->key),
            '',
            OPENSSL_RAW_DATA,
            'AES-256-ECB'
        );
    }

    /**
     * @param array $message
     * @throws InvalidSignException
     */
    protected function validate(array $message)
    {
        $token = $this->app->config->get('token');
        $msg_signature = $message['msg_signature'];

        $rList = array();

        array_push($rList, $message['timestamp']);
        array_push($rList, $message['nonce']);
        array_push($rList, $message['msg']);
        array_push($rList, $token);
        sort($rList, 2);

        $signature = sha1(implode('', $rList));

        if ($signature != $msg_signature) {
            throw new InvalidSignException();
        }

    }

    /**
     * @param mixed $result
     */
    protected function strict($result)
    {
        if (true !== $result && isset($this->fail)) {
            $this->fail(strval($result));
        }
    }
}
