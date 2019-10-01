<?php

declare(strict_types=1);

namespace MnToolkit;

use Aws\Sqs\SqsClient;

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

}
