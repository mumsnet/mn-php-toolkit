<?php

declare(strict_types=1);

namespace mumsnet\mn_toolkit\Unit;

use MnToolkit\UserSessionsLaravel;
use Tests\TestCase;

class UserSessionLaravelTest extends TestCase
{
    /**
     * Testing get , set user session and delete
     * @test
     */
    public function testUserSession()
    {
        $session = new UserSessionsLaravel();

        $cookieName =  $session->setUserSession(['user_id'=>'11111111'], true);

        $this->assertNotFalse($cookieName);

        $session = new UserSessionsLaravel(['mnsso'=>$cookieName]);
        $user= $session->getUserSession();

        $this->assertEquals('11111111',$user->user_id);
        $session->deleteUserSession();
    }

    /**
     * Testing get user id , set user session and delete
     * @test
     */
    public function testUserIdSession()
    {
        $session = new UserSessionsLaravel();

        $cookieName =  $session->setUserSession(['user_id'=>'11111111'], true);

        $this->assertNotFalse($cookieName);

        $session = new UserSessionsLaravel(['mnsso'=>$cookieName]);
        $user_id= $session->getUserIdFromSession();

        $this->assertEquals('11111111',$user_id);
        $session->deleteUserSession();
    }


}
