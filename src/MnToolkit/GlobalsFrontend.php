<?php
declare(strict_types=1);

namespace MnToolkit;

class GlobalsFrontend extends MnToolkitBase
{
    public function getFragments($cacheSeconds = 60)
    {
        if (getenv("SRV_GLOBALS_URL")) {
            $json = $this->cachedHttpGet(getenv("SRV_GLOBALS_URL"), $cacheSeconds, [], false);
        } else {
            $json = null;
        }

        if (is_null($json)) {
            return $this->globalsHtmlFallback();
        }

        return json_decode($json);
    }

    private function globalsHtmlFallback()
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
