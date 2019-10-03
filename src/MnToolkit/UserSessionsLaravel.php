<?php

declare(strict_types=1);

namespace MnToolkit;

use Illuminate\Support\Facades\Redis;

class UserSessionsLaravel
{
    /**
     * Create a new Skeleton Instance
     */
    public function __construct()
    {
      
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
            $user = $this->getUserSession(array $cookies);
        }

        return json_encode($user['user_id']);
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
            $user = Redis::get($cookies['SSO_COOKIE_NAME']);
        }

        return json_encode($user);
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

        $cookies['SSO_COOKIE_NAME'] = [
            'values' => env('SSO_COOKIE_VALUE_PREFIX'). uniqid(),
            'expires' => $expiry,
            'secure' => true,
            'httponly' => true
        ];

        Redis::set($cookies['SSO_COOKIE_NAME'], $user_id, $expiry);
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
            Redis::del($cookies['SSO_COOKIE_NAME']);
        }
    }

}
