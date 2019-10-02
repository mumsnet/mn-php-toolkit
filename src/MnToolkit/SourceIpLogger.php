<?php

declare(strict_types=1);

namespace MnToolkit;

class SourceIpLogger
{
    /**
     * Get Origin request Id and log it - set it as request Id for every request
     *
     * @param request $request
     *
     */
    public function sourceIpLogger(request $request, Closure $next)
    {
        if(env('HTTP_X_FORWARDED_FOR')){
            $stringsArray = explode(",", "HTTP_X_FORWARDED_FOR");
            $remote_ip = $stringsArray[0] ?? '';
            env('REMOTE_ADDR') = env("HTTP_X_FORWARDED_FOR") = $remote_ip;
            return $next($request);
        }else{
            return $next($request);
        }
    }

}
