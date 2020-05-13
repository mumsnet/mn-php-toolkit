<?php
declare(strict_types=1);

namespace MnToolkit;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;

class GlobalsFrontend
{
    private $logger;
    private $client;

    public function __construct($client)
    {
        $logger = GlobalLogger::getInstance()->getLogger();

        if (is_null($logger)) {
            $logger = new Logger(get_class($this));
            $logger->pushHandler(new ErrorLogHandler());
        }

        $this->logger = $logger;
        $this->client = $client;
    }

    public function getComponents($options, $cache = FileCache::class)
    {
        $cachedGlobalsTimeout = 900; // Set timeout to 15 mins (900 seconds)
        $cachedFallbackTimeout = 60; // Set timeout to 1m (60 seconds)
        $cacheKey = 'globals';

        try {
            if (getenv('SRV_GLOBALS_URL')) {
                $cachedGlobals = $cache::getInstance()->get($cacheKey);
                if ($cachedGlobals) {
                    return json_decode($cachedGlobals);
                }
                $response = $this->client->get(getenv('SRV_GLOBALS_URL'), ['timeout' => 3, 'query' => $options]);
                if ($response->getStatusCode() == 200) {
                    $globals = $response->getBody()->getContents();
                    $cache::getInstance()->set($cacheKey, $globals, $cachedGlobalsTimeout);
                    return json_decode($globals);
                } else {
                    $this->logger->error('globals service request failed ' . $response->getStatusCode());
                    $fallback = $this->fallbackHtml();
                    $cache::getInstance()->set($cacheKey, json_encode($fallback), $cachedFallbackTimeout);
                    return $fallback;
                }
            } else {
                $this->logger->error('Environment variable SRV_GLOBALS_URL does not exist.');
                return $this->fallbackHtml();
            }
        } catch (RequestException $e) {
            $this->logger->error('globals service request failed ' . Psr7\str($e->getRequest()));
            return $this->fallbackHtml();
        }
    }

    private function fallbackHtml()
    {
        $cdnUrl = getenv('CDN_URL');

        $fallback = new \stdClass();
        $fallback->headScripts = '
            <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
            <script src="' . $cdnUrl . 'global-assets/js/global.min.js"></script>
            <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
            <link href="' . $cdnUrl . 'global-assets/css/global.min.css" rel="stylesheet">
        ';
        $fallback->bodyScripts = '';
        $fallbackHeader = '
            <header class="d-flex justify-content-between align-items-center service-fallback__header">
            <a href="/">
                <img class="ml-2 my-1" src="' . $cdnUrl . 'global-assets/images/logos/mn-circle-logo.png" alt="Mumsnet" width="44">
            </a>
            <div>
                <a class="service-fallback__nav-link text-white border-right border-white" href="/">Home</a>
                <a class="service-fallback__nav-link text-white border-right border-white" href="/Talk">Talk</a>
                <a class="service-fallback__nav-link text-white mr-2" href="/signin">My account</a>
            </div>
            </header>
            <div class="p-2 mb-5">
                <div role="alert" class="alert-banner alert-banner--warning">
                    <div class="d-flex">
                        <span class="alert-banner__icon alert-banner__icon--warning">
                            <i class="fa fa-exclamation"></i>
                        </span>
                        <div>
                            <p class="h5"> Oops! </p>
                            <p class="font-bold"> We\'re having a few technical problems and our site might not work as it should.</p>
                            <p> Please bear with us, we\'re working hard on this and will have it fixed as quick as we can.</p>
                        </div>
                    </div>
                </div>
            </div>
        ';
        $fallback->headerLoggedIn = $fallbackHeader;
        $fallback->headerLoggedOut = $fallbackHeader;
        $fallback->footer = '
            <footer class="service-fallback__footer p-2 mt-7">
                <a class="service-fallback__nav-link text-white border-right border-white" href="/">Home</a>
                <a class="service-fallback__nav-link text-white border-right border-white" href="/Talk">Talk</a>
                <a class="service-fallback__nav-link text-white mr-2" href="/signin">My account</a>
            </footer>
        ';

        return $fallback;
    }
}
