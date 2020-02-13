<?php
declare(strict_types=1);
namespace MnToolkit;
use Illuminate\Support\Facades\Redis as RedisClient;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Exception;
use Illuminate\Support\Facades\Cookie;
class UserSessionsLaravel
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
    }
    /**
     * Get User Session from Redis
     *
     * @throws Exception
     */
    public function getUserIdFromSession()
    {
        if (empty($this->cookies)) {
            $this->logger->error("Cookie Array Was Empty");
            throw new Exception('Cookie array is empty');
        }
        $user = $this->getUserSession();
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
            $this->logger->error("Cookie Array Was Empty");
            throw new Exception('Cookie array is empty');
        }
        if(!isset($this->cookies[$this->cookie_name])){
            return false;
        }
        $user = RedisClient::get($this->cookies[$this->cookie_name]);
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
    public function setUserSession($user, $persistent, $other_attributes = [])
    {
        $uniqueId = uniqid();
        $expiry = $persistent ? strtotime("+1 year") : strtotime("+1 day");
        //tell laravel to add the cookie to the user's browser - Queue adds the cookie to the next response
        try {
            Cookie::queue($this->cookie_name, $this->cookie_value_prefix .$uniqueId, $expiry);
            RedisClient::set($this->cookie_value_prefix .$uniqueId, json_encode($user), "px", $expiry);
            return $this->cookies[$this->cookie_name];
        } catch (Exception $e) {
            GlobalLogger::getInstance()->getLogger()->error("Failed to set cookie and redis session: ". $e->getMessage());
            return false;
        }
    }

    /**
     * Delete User Session from Redis
     *
     * @throws Exception
     */
    public function deleteUserSession()
    {
        if (empty($this->cookies)) {
            $this->logger->error("Cookie Array Was Empty");
            throw new Exception('Cookies array is empty');
        }
        //tell laravel to delete from user's browser - Queue adds the cookie to the next response
        Cookie::queue(Cookie::forget($this->cookie_name));
        RedisClient::del($this->cookies[$this->cookie_name]);
    }
}
