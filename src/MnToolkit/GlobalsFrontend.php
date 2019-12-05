<?php
declare(strict_types=1);

namespace MnToolkit;

class GlobalsFrontend extends MnToolkitBase
{
    public function getFragments($cacheSeconds = 60)
    {
        if (getenv("SRV_GLOBALS_URL")) {
            $json = json_decode($this->cachedHttpGet(getenv("SRV_GLOBALS_URL"), $cacheSeconds, [], false));
        } else {
            $json = null;
        }

        if (json_last_error() !== JSON_ERROR_NONE || is_null($json)) {
            return $this->globalsHtmlFallback();
        }

        return $json;
    }

    private function globalsHtmlFallback()
    {
        $cdnUrl = getenv('CDN_URL');
        $appUrl = getenv('APP_URL');

        $fallback = new \stdClass();
        $fallback->headScripts = '<link rel="stylesheet" href="' . $cdnUrl  . 'global-assets/css/global.min.css">';
        $fallback->bodyScripts = '';
        $fallback->headerLoggedIn = '<header class="bg-light p-3"><a href="' . $appUrl . '">Mumsnet</a></header>';
        $fallback->headerLoggedOut = '<header class="bg-light p-3"><a href="' . $appUrl . '">Mumsnet</a></header>';
        $fallback->footer = '<footer class="bg-light p-3"><small>&#169; ' . date('Y') . ' Mumsnet Ltd.</small></footer>';

        return $fallback;
    }
}
