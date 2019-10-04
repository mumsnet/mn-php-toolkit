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
      $this->cookie_name = 'mmsso';
      $this->cookie_value_prefix = 'mmsso_';
    }

    /**
     * Get User Session from Redis
     *
     * @param request $request
     *
     */
    public function getUserIdFromSession(array $cookies)
    {
        if(!empty($cookies)){
            $user = $this->getUserSession($cookies);
        }

        return $user['user_id'];
    }

    /**
     * Get User Session from Redis
     *
     * @param request $request
     *
     */
    public function getUserSession(array $cookies)
    {
        if(!empty($cookies)){
            $user = Redis::get($cookies[$this->cookie_name]);
        }

        return $user;
    }

    /**
     * Set User Session in Redis
     *
     * @param request $request
     *
     */
    public function setUserSession(array $cookies, $user_id, $persistent, $other_attributes = {})
    {
        $expiry = $persistent ? strtotime("+1 year") : strtotime("+1 day");

        $cookies[$this->cookie_name] = [
            'values' => $this->cookie_value_prefix. uniqid(),
            'expires' => $expiry,
            'secure' => true,
            'httponly' => true
        ];

        Redis::set($cookies[$this->cookie_name], $user_id, $expiry);
    }

    /**
     * Delete User Session from Redis
     *
     * @param request $request
     *
     */
    public function deleteUserSession(array $cookies)
    {
        if($cookies){
            Redis::del($cookies[$this->cookie_name]);
        }
    }

}
