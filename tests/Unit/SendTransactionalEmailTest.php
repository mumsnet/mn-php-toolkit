<?php

declare(strict_types=1);

namespace mumsnet\mn_toolkit\Unit;

use MnToolkit\sendTransactionalEmail;
use Tests\TestCase;

class SendTransactionalEmailTest extends TestCase
{
    /**
     * Testing send transactional email
     * @test
     */
    public function sendTransactionalEmail()
    {
        $mn_transctional_email = new sendTransactionalEmail();
        $template ='NT_TEST_MAIL';
        $body = [];
        $email = env('DEV_EMAIL');
        $sent = $mn_transctional_email->sendTransactionalEmail($template, $email, 'Transactional Email Toolkit Unit Test', 'Hello panos',
            ['body' => $body], ' ', '127.0.0.1');

       
        $this->assertEquals('200',$sent['@metadata']['statusCode']);
    }

}
