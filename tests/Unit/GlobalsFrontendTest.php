<?php

declare(strict_types=1);

namespace mumsnet\mn_toolkit\Unit;

use MnToolkit\GlobalsFrontend;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Tests\TestCase;

class MockFileCache
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new MockFileCache();
        }

        return self::$instance;
    }

    public function get() { }

    public function set() { }
}

class GlobalsFrontendTest extends TestCase
{
    /** @test */
    public function can_get_component_html_from_globals_json()
    {
        $mock = new MockHandler([
            new Response(200, [], "{\"headScripts\":\"<h1>Scripts</h1>\",\"headerLinks\":\"<p>Links</p>\"}")
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $globalsFrontend = new GlobalsFrontend($client);
        $html = $globalsFrontend->getComponents(['headerLoggedIn' => 'true', 'headScripts' => 'true']);

        $this->assertObjectHasAttribute('headScripts', $html);
        $this->assertObjectHasAttribute('headerLinks', $html);
        $this->assertEquals('<h1>Scripts</h1>', $html->headScripts);
        $this->assertEquals('<p>Links</p>', $html->headerLinks);
    }

    /** @test */
    public function returns_fallback_html_if_response_is_not_200()
    {
        $mock = new MockHandler([new Response(404)]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $cache = new MockFileCache();

        $globalsFrontend = new GlobalsFrontend($client);
        $html = $globalsFrontend->getComponents(['doesNotExist' => 'true'], $cache);

        $this->assertObjectHasAttribute('headScripts', $html);
        $this->assertObjectHasAttribute('bodyScripts', $html);
        $this->assertObjectHasAttribute('headerLoggedIn', $html);
        $this->assertObjectHasAttribute('headerLoggedOut', $html);
        $this->assertObjectHasAttribute('footer', $html);
    }
}
