<?php

declare(strict_types=1);

namespace MnToolkit;

use Exception;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Predis\Predis;
use Psr\Log\LoggerInterface;

class UserSessionsLambda
{

    public function __construct($cookies = [])
    {
        $logger = GlobalLogger::getInstance()->getLogger();

        if (is_null($logger)) {
            $logger = new Logger(get_class($this));
            $logger->pushHandler(new ErrorLogHandler());
        }

        $this->cookie_name = 'mnsso';
        $this->cookie_value_prefix = 'mnsso_';
        $this->cookies = $cookies;

        $this->logger = $logger;

        $this->redis = new Predis\Client(array(
            "scheme" => "tcp",
            "host" => getenv('MN_REDIS_URL'),
            "port" => 6379
        ));

    }

    /**
     * Get User Session from Redis
     *
     * @throws Exception
     */
    public function getUserIdFromSession()
    {
        if (empty($this->cookies)) {
            $this->logger->error("Cookie array is empty");
            throw new Exception('Cookie array is empty');
        }

        $user = $this->getUserSession($this->cookies);

        if (!$user) {
            $this->logger->error("No user could be obtained from the session");
            throw new Exception('No user could be obtained from the session');
        }

        return $user->user_id;
    }

    /**
     * Get User Session from Redis
     *
     * @throws Exception
     */
    public function getUserSession()
    {
        if (empty($this->cookies)) {
            $this->logger->error("Cookie array is empty");
            throw new Exception('Cookie array is empty');
        }

        $user = $this->redis->get($this->cookies[$this->cookie_name]);

        if (!$user) {
            $this->logger->error("No user could be obtained from the session");
            throw new Exception('No user could be obtained from the session');
        }

        return json_decode($user);
    }

    /**
     * Set User Session in Redis
     *
     *
     */
    public function setUserSession($user_id, $persistent, $other_attributes = [])
    {
        $expiry = $persistent ? strtotime("+1 year") : strtotime("+1 day");

        $this->cookies[$this->cookie_name] = [
            'values' => $this->cookie_value_prefix . uniqid(),
            'expires' => $expiry,
            'secure' => true,
            'httponly' => true
        ];

        $this->redis->set($this->cookies[$this->cookie_name], $user_id, $expiry);

        return $this->cookies;
    }

    /**
     * Delete User Session from Redis
     *
     * @throws Exception
     */
    public function deleteUserSession()
    {
        if (empty($this->cookies)) {
            $this->logger->error("Cookie array is empty");
            throw new Exception('Cookies array is empty');
        }

        $this->redis->del($this->cookies[$this->cookie_name]);

        return $this->cookies;
    }

}
