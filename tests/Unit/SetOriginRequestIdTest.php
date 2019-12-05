<?php

declare(strict_types=1);

namespace mumsnet\mn_toolkit\Unit;

use MnToolkit\SetOriginRequestId;
use Illuminate\Http\Request;
use Tests\TestCase;

class SetOriginRequestIdTest extends TestCase
{
    /**
     * Testing set origin request id
     * @test
     */
    public function setOriginRequestId()
    {
        $setOriginId = new SetOriginRequestId();
        $request = new Request;

        $setId = $setOriginId->setOriginRequestId($request,function ($request) {return $request;});

        $headerValue = $setId->headers->get('X-Request-Id');
        $this->assertTrue(isset($headerValue) && is_string($headerValue));
    }

}
