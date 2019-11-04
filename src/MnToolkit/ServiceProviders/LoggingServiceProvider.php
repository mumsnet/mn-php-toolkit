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
        if (getenv('HTTP_X_REQUEST_ID')) {
            $request_id = getenv('HTTP_X_REQUEST_ID');
        } elseif (getenv('HTTP_X_AMZN_TRACE_ID')) {
            preg_match('/^.*Root=([^;]*).*$/', getenv('HTTP_X_AMZN_TRACE_ID'), $matches);
            $request_id = $matches[0];
        }

        $request_id = $request_id ?? uniqid();

        $monolog = Log::getMonolog();

        $monolog->pushProcessor(function ($record, $request_id) {
            $record['origin_request_ip'] = Request::ip();
            $record['origin_request_id'] = $request_id;
            return $record;
        });

    }
}
