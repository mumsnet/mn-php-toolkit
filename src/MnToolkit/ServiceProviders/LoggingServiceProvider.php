<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Getting Request ID
        if ($_SERVER['HTTP_X_REQUEST_ID']) {
            $request_id = $_SERVER['HTTP_X_REQUEST_ID'];
        } elseif ($_SERVER['HTTP_X_AMZN_TRACE_ID']) {
            preg_match('/^.*Root=([^;]*).*$/', $_SERVER['HTTP_X_AMZN_TRACE_ID'], $matches);
            $request_id = $matches[0];
        }
        $request_id = $request_id ?? uniqid();

        //Getting Request IP
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $stringsArray = explode(",", "HTTP_X_FORWARDED_FOR");
            $request_ip = $stringsArray[0] ?? '';
        }

        $monolog = Log::getMonolog();

        //Adding both to monolog
        $monolog->pushProcessor(function ($record, $request_id,$request_ip) {
            $record['origin_request_ip'] = $request_ip ?? '';
            $record['origin_request_id'] = $request_id;
            return $record;
        });

    }
}
