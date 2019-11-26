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
            return null;
        }

        return json_decode($json);
    }
}
