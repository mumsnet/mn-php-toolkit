<?php

namespace MnToolkit\ServiceProviders;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use MnToolkit\GlobalLogger;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;

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
        if (isset($_SERVER['HTTP_X_REQUEST_ID'])) {
            $request_id = $_SERVER['HTTP_X_REQUEST_ID'];
        } elseif (isset($_SERVER['HTTP_X_AMZN_TRACE_ID'])) {
            preg_match('/^.*Root=([^;]*).*$/', $_SERVER['HTTP_X_AMZN_TRACE_ID'], $matches);
            $request_id = $matches[0];
        }
        $request_id = $request_id ?? uniqid();

        //Getting Request IP
        $request_ip='';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $stringsArray = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
            $request_ip = $stringsArray[0] ?? '';
        }

        $logger = GlobalLogger::getInstance()->getLogger();

        if (is_null($logger)) {
            $logger = new Logger(get_class($this));
            $logger->pushHandler(new ErrorLogHandler());
        }

        $logger->pushProcessor(function ($record) use ($request_id, $request_ip) {
            $record['origin_request_ip'] = $request_ip;
            $record['origin_request_id'] = $request_id;
            return $record;
        });

    }
}
