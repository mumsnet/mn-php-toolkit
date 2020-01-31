<?php


namespace MnToolkit;

use Ackintosh\Ganesha\Builder;
use Ackintosh\Ganesha\GuzzleMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class WiredHttpClient
{
    private static $instance = null;

    private $handlers = null;

    /**
     * WiredHttpClient singleton accessor.  Returns the single instance of WiredHttpClient
     */
    public static function getInstance(): WiredHttpClient
    {
        if (self::$instance == null) {
            self::$instance = new WiredHttpClient();
        }

        return self::$instance;
    }

    /**
     * @param  array  $options the guzzle client creation options
     * @return GuzzleHttp\Client a new guzzle client, already wired into the circuit breaker
     * When using this client, if you get a Ackintosh\Ganesha\Exception\RejectedException
     * this indicates the circuit is open.
     */
    public function newClient($options = []): \GuzzleHttp\Client
    {
        return new Client(array_merge(['handler' => $this->handlers], $options));
    }

    /**
     * Private constructor.  Only called from getInstance.
     */
    private function __construct()
    {
        $adapter = new \MnToolkit\GaneshaFileStoreAdapter();

        $ganesha = Builder::build([
            'timeWindow' => 30,
            'failureRateThreshold' => 50,
            'minimumRequests' => 10,
            'intervalToHalfOpen' => 5,
            'adapter' => $adapter
        ]);
        $middleware = new GuzzleMiddleware($ganesha);

        $this->handlers = HandlerStack::create();
        $this->handlers->push($middleware);
    }

}
