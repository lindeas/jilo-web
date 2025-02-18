<?php

require_once dirname(__DIR__, 4) . '/app/classes/router.php';

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router('', true); // Empty controller path and dry-run mode
    }

    public function testAddRoute()
    {
        $this->router->add('/test', 'TestController@index');
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testDispatchRoute()
    {
        $this->router->add('/users/(\d+)', 'UserController@show');

        $match = $this->router->dispatch('/users/123');
        $this->assertIsArray($match);
        $this->assertEquals('UserController@show', $match['callback']);
        $this->assertEquals(['123'], $match['params']);
    }

    public function testDispatchRouteWithMultipleParameters()
    {
        $this->router->add('/users/(\d+)/posts/(\d+)', 'PostController@show');

        $match = $this->router->dispatch('/users/123/posts/456');
        $this->assertIsArray($match);
        $this->assertEquals('PostController@show', $match['callback']);
        $this->assertEquals(['123', '456'], $match['params']);
    }

    public function testNoMatchingRoute()
    {
        $this->router->add('/test', 'TestController@index');

        $match = $this->router->dispatch('/nonexistent');
        $this->assertNull($match);
    }

    public function testDispatchWithQueryString()
    {
        $this->router->add('/test', 'TestController@index');

        $match = $this->router->dispatch('/test?param=value');
        $this->assertIsArray($match);
        $this->assertEquals('TestController@index', $match['callback']);
        $this->assertEquals([], $match['params']);
    }

    public function testOptionalParameters()
    {
        $this->router->add('/users(?:/(\d+))?', 'UserController@index');

        // Test with parameter
        $match1 = $this->router->dispatch('/users/123');
        $this->assertIsArray($match1);
        $this->assertEquals('UserController@index', $match1['callback']);
        $this->assertEquals(['123'], $match1['params']);

        // Test without parameter
        $match2 = $this->router->dispatch('/users');
        $this->assertIsArray($match2);
        $this->assertEquals('UserController@index', $match2['callback']);
        $this->assertEquals([], $match2['params']);
    }

    public function testInvokeWithMissingController()
    {
        $router = new Router(''); // Empty controller path, not in dry-run mode
        ob_start();
        $router->dispatch('/test');
        $output = ob_get_clean();
        $this->assertEquals('404 page not found', $output);
    }
}
