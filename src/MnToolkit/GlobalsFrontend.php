<?php
declare(strict_types=1);

namespace MnToolkit;

class GlobalsFrontend extends MnToolkitBase
{
    public function getFragments()
    {
        $json = $this->cachedHttpGet(getenv("GLOBALS_URL"), 60, [], false);
        if (is_null($json)) {
            return null;
        }
        return json_decode($json);
    }
}
