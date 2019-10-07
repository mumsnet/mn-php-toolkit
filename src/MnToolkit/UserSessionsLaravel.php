<?php

declare(strict_types=1);

namespace MnToolkit;

use Illuminate\Support\Facades\Redis;

class UserSessionsLaravel
{
    /**
     * Create a new Skeleton Instance
     */
    public function __construct($cookies)
    {
      $this->cookie_name = 'mnsso';
      $this->cookie_value_prefix = 'mnsso_';
      $this->cookies = $cookies;

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

        return $user['user_id'];
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

            $user = Redis::get($this->cookies[$this->cookie_name]);
        }

        return $user;
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


        Redis::set($this->cookies[$this->cookie_name], $user_id, $expiry);
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
            Redis::del($this->cookies[$this->cookie_name]);
        }
    }

}
