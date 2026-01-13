<?php

namespace Tests\Unit\Core;

use App\Core\PluginRouteRegistry;
use PHPUnit\Framework\TestCase;

class PluginRouteRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        PluginRouteRegistry::reset();
    }

    protected function tearDown(): void
    {
        PluginRouteRegistry::reset();
        parent::tearDown();
    }

    /**
     * Registering a prefix should make it discoverable via match() and the
     * prefix should be appended to allowed/public lists as configured.
     */
    public function testRegisterPrefixAndInjection(): void
    {
        $callable = static function () {};
        PluginRouteRegistry::registerPrefix('Calls', [
            'dispatcher' => $callable,
            'access' => 'public',
        ]);

        $definition = PluginRouteRegistry::match('calls');
        $this->assertIsArray($definition);
        $this->assertSame($callable, $definition['dispatcher']);

        $allowed = PluginRouteRegistry::injectAllowedPages(['dashboard']);
        $this->assertContains('calls', $allowed);

        $public = PluginRouteRegistry::injectPublicPages(['login']);
        $this->assertContains('calls', $public);
    }

    /**
     * Dispatch should pass the action to the registered callable.
     */
    public function testDispatchWithCallable(): void
    {
        $captured = [];
        PluginRouteRegistry::registerPrefix('reports', [
            'dispatcher' => function ($action, array $context) use (&$captured) {
                $captured = [$action, $context];
                return true;
            },
        ]);

        $context = ['request' => ['action' => 'list'], 'foo' => 'bar'];
        $handled = PluginRouteRegistry::dispatch('reports', $context);

        $this->assertTrue($handled);
        $this->assertSame('list', $captured[0]);
        $expectedContext = $context;
        $expectedContext['action'] = 'list';
        $this->assertSame($expectedContext, $captured[1]);
    }

    /**
     * Dispatch should instantiate classes with a handle() method.
     */
    public function testDispatchWithClassHandler(): void
    {
        PluginRouteRegistry::registerPrefix('exports', [
            'dispatcher' => FakeRouteHandler::class,
        ]);

        $handled = PluginRouteRegistry::dispatch('exports', ['request' => ['action' => 'download']]);
        $this->assertTrue($handled);
        $this->assertSame(['exports', 'download'], FakeRouteHandler::$handledCalls);
    }
}

class FakeRouteHandler
{
    public static array $handledCalls = [];

    public function handle(string $action, array $context)
    {
        self::$handledCalls = [$context['page'] ?? 'exports', $action];
        return true;
    }
}
