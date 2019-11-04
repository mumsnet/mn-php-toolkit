<?php

declare(strict_types=1);

namespace MnToolkit;

use Closure;

class SetOriginRequestId
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get Origin request Id and log it - set it as request Id for every request
     *
     *
     */
    public function setOriginRequestId($request, Closure $next)
    {
        //TODO inlcude the request Id in every log line similar to sourceiplogger
        if (getenv('HTTP_X_REQUEST_ID')) {
            $request_id = getenv('HTTP_X_REQUEST_ID');
            $this->logger->info("Found HTTP_X_REQUEST_ID: $request_id");
        } elseif (getenv('HTTP_X_AMZN_TRACE_ID')) {
            preg_match('/^.*Root=([^;]*).*$/', getenv('HTTP_X_AMZN_TRACE_ID'), $matches);
            $request_id = $matches[0];
            $this->logger->info("Found HTTP_X_AMZN_TRACE_ID: $request_id");
        }
        if (!$request_id) {
            $request_id = uniqid();
            $this->logger->info("Set request_id: $request_id");
        }

        $response = $next($request);

        $response->header('X-Request-Id', $request_id, false);

        return $response;
    }

}
