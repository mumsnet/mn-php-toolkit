<?php

declare(strict_types=1);

namespace MnToolkit;

require "predis/autoload.php";


class UserSessionsLambda
{
    
    public function __construct($cookies)
    {
      $this->cookie_name = 'mnsso';
      $this->cookie_value_prefix = 'mnsso_';
      $this->cookies = $cookies;

      PredisAutoloader::register();

      $this->redis = new PredisClient(array(
            "scheme" => "tcp",
            "host" => env('MN_REDIS_URL'),
            "port" => 6379
        ));

    }

    /**
     * Get User Session from Redis
     *
     * @param request $request
     *
     */
    public function getUserIdFromSession()
    {
        if(!empty($this->cookies)){
            $user = $this->getUserSession($this->cookies);
        }

        return $user->user_id;
    }

    /**
     * Get User Session from Redis
     *
     * @param request $request
     *
     */
    public function getUserSession()
    {
        if(!empty($this->cookies)){

            $user = $this->redis->get($this->cookies[$this->cookie_name]);
        }

        return json_decode($user);
    }

    /**
     * Set User Session in Redis
     *
     * @param request $request
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
     * @param request $request
     *
     */
    public function deleteUserSession()
    {
        if($this->cookies){
            $this->redis->del($this->cookies[$this->cookie_name]);
        }
    }

}
