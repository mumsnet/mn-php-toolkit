<?php

declare(strict_types=1);

namespace MnToolkit;

class ToolkitClass
{
    /**
     * Create a new Skeleton Instance
     */
    public function __construct()
    {

    }

    /**
     * Friendly welcome
     *
     * @param  string  $phrase  Phrase to return
     *
     * @return string Returns the phrase passed in
     */
    public function echoPhrase(string $phrase): string
    {
        return $phrase;
    }

}
