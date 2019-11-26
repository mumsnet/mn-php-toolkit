<?php
declare(strict_types=1);

namespace MnToolkit;

class GlobalsFrontend extends MnToolkitBase
{
    public function getFragments($globalsUrl)
    {
        $json = $this->cachedHttpGet($globalsUrl, 60, [], false);
        if (is_null($json)) {
            return null;
        }
        return json_decode($json);
    }
}
