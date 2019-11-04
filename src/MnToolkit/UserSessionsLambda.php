<?php

declare(strict_types=1);

namespace MnToolkit;

require "predis/autoload.php";
use Exception;
// TODO Add the predis package instead
//TODO add the logger here

class UserSessionsLambda
{
    
    public function __construct($cookies , LoggerInterface $logger)
    {
      $this->cookie_name = 'mnsso';
      $this->cookie_value_prefix = 'mnsso_';
      $this->cookies = $cookies;

      $this->logger = $logger;

      PredisAutoloader::register();

      $this->redis = new PredisClient(array(
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
            throw new Exception('Cookie array is empty');
        }

        $user = $this->getUserSession($this->cookies);

        if (!$user) {
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
            throw new Exception('Cookie array is empty');
        }

        $user = $this->redis->get($this->cookies[$this->cookie_name]);

        if (!$user) {
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
            'values' => $this->cookie_value_prefix. uniqid(),
            'expires' => $expiry,
            'secure' => true,
            'httponly' => true
        ];


        $this->redis->set($this->cookies[$this->cookie_name], $user_id, $expiry);
    }

    /**
     * Delete User Session from Redis
     *
     * @throws Exception
     */
    public function deleteUserSession()
    {
        if (empty($this->cookies)) {
            throw new Exception('Cookies array is empty');
        }

        $this->redis->del($this->cookies[$this->cookie_name]);

    }

}
