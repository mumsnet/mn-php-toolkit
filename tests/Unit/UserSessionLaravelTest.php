<?php

declare(strict_types=1);

namespace mumsnet\mn_toolkit\Unit;

use MnToolkit\UserSessionsLaravel;

class UserSessionLaravelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test that true does in fact equal true
     */
    public function testSetUserSession()
    {
        $session = new UserSessionsLaravel();

        $passed =  $session->setUserSession('11111111', true);

        $this->assertTrue($passed);
    }
}
