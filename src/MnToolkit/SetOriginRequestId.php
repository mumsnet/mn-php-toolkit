<?php

declare(strict_types=1);

namespace MnToolkit;

use Aws\Sqs\SqsClient;
use MnMonolog\Handler\PapertrailHandler;

class SetOriginRequestId
{
    /**
     * Get Origin request Id and log it - set it as request Id for every request
     *
     * @param request $request
     *
     */
    public function setOriginRequestId(request $request, Closure $next)
    {
        if(env('HTTP_X_REQUEST_ID')){
            $request_id = env('HTTP_X_REQUEST_ID');
            //Log "Found HTTP_X_REQUEST_ID #{request_id}"
        }
        if(env('HTTP_X_AMZN_TRACE_ID')){
            preg_match('/^.*Root=([^;]*).*$/',env('HTTP_X_AMZN_TRACE_ID'),$matches);
            $request_id = env('HTTP_X_REQUEST_ID') = $matches[0];
            //Log "Found HTTP_X_AMZN_TRACE_ID #{request_id}"
        }
        if(!$request_id){
            $request_id = env('HTTP_X_REQUEST_ID') = uniqid();
            //Log ""Set request_id #{request_id}""
        }

        $response = $next($request);

        $response->header('X-Request-Id', $request_id, false);

        return $response;
    }

}
