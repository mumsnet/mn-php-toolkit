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
        $response = $next($request);
        if(env('HTTP_X_FORWARDED_FOR')){
            $stringsArray = explode(",", "HTTP_X_FORWARDED_FOR");
            $remote_ip = $stringsArray[0] ?? '';
            env('REMOTE_ADDR') = env("action_dispatch.remote_ip") = env("HTTP_X_FORWARDED_FOR") = $remote_ip;
            $_REQUEST['remote_ip'] = $remote_ip;
            return $response;
        }else{
            return $response;
        }
    }

}
