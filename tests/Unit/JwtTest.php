<?php

declare(strict_types=1);

namespace mumsnet\mn_toolkit\Unit;

use MnToolkit\JWT;
use Illuminate\Http\Request;
use Tests\TestCase;

class JwtTest extends TestCase
{
    /**
     * Testing set origin request id
     * @test
     */
    public function testIsValid()
    {
        $isValid =  JWT::getInstance()->isValidToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRfaWQiOiJyYWlsc19sZWdhY3kifQ.Dciipit-lh9N5FuPLNviTU-4Q5PUIdweJOCmPg7ucN4');
        $this->assertTrue($isValid);
    }



}
