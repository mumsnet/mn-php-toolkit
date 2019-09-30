<?php

declare(strict_types=1);

namespace mumsnet\mn-toolkit;

class ToolkitClass
{
    /**
     * Create a new Skeleton Instance
     */
    public function __construct()
    {
      
    }
    

    /**
     * Friendly welcome
     *
     * @param string $phrase Phrase to return
     *
     * @return string Returns the phrase passed in
     */
    public function echoPhrase(string $phrase): string
    {
        return $phrase;
    }

    /**
     * Get Origin request Id and log it - set it as request Id for every request
     *
     * @param request $request
     *
     */
    public function setOriginRequestId(request $request): void
    {
        
    }

    /**
     * Get Origin request Id and log it - set it as request Id for every request
     *
     * @param request $request
     *
     */
    public function sourceIpLogger(request $request): void
    {
        
    }

    /**
     * Get User Session from Redis
     *
     * @param request $request
     *
     */
    public function getUserSession(array $cookies): boolean
    {
        
    }

    /**
     * Set User Session in Redis
     *
     * @param request $request
     *
     */
    public function setUserSession(array $cookies): boolean
    {
        
    }

    /**
     * Delete User Session from Redis
     *
     * @param request $request
     *
     */
    public function deleteUserSession(array $cookies): boolean
    {
        
    }

    /**
     * Send Transactional Email 
     *
     * @param request $request
     *
     */
    public function sendTransactionalEmail($body,$subject,$email)
    {
        
    }

}
