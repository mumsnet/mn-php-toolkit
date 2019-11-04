<?php

declare(strict_types=1);

namespace MnToolkit;

use Illuminate\Support\Facades\Redis;
use Exception;

class UserSessionsLaravel
{
    public function __construct($cookies)
    {
        $this->cookie_name = 'mnsso';
        $this->cookie_value_prefix = 'mnsso_';
        $this->cookies = $cookies;

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

        $user = $this->getUserSession();

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
        $user = Redis::get($this->cookies[$this->cookie_name]);

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
            'values' => $this->cookie_value_prefix . uniqid(),
            'expires' => $expiry,
            'secure' => true,
            'httponly' => true
        ];

        //tell laravel to add the cookie to the user's browser - Queue adds the cookie to the next response
        \Cookie::queue($this->cookie_name, $this->cookie_value_prefix . uniqid(), $expiry);

        Redis::set($this->cookies[$this->cookie_name], $user_id, $expiry);
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

        //tell laravel to delete from user's browser - Queue adds the cookie to the next response
        \Cookie::queue(\Cookie::forget($this->cookie_name));

        Redis::del($this->cookies[$this->cookie_name]);

    }

}
