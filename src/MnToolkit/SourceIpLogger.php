<?php

declare(strict_types=1);

namespace MnToolkit;

use Closure;

class SourceIpLogger
{
    /**
     * Get Origin request Id and log it - set it as request Id for every request
     *
     * @param $request
     *
     */
    public function sourceIpLogger($request, Closure $next)
    {
        //TODO find laravel way of getting the remote IP and override it
        //TODO find a way to include the IP in every log line
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $stringsArray = explode(",", "HTTP_X_FORWARDED_FOR");
            $remote_ip = $stringsArray[0] ?? '';
            putenv("REMOTE_ADDR=$remote_ip");
            putenv("HTTP_X_FORWARDED_FOR=$remote_ip");
            return $next($request);
        } else {
            return $next($request);
        }
    }

}
