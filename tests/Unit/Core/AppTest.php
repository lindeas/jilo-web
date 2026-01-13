<?php

namespace Tests\Unit\Core;

use App\App;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        App::reset();
    }

    protected function tearDown(): void
    {
        App::reset();
        parent::tearDown();
    }

    /**
     * Basic happy path: storing and retrieving services via the registry.
     */
    public function testSetAndGetService(): void
    {
        App::set('foo', 'bar');
        $this->assertTrue(App::has('foo'));
        $this->assertSame('bar', App::get('foo'));
    }

    /**
     * Missing services should fall back to the provided default value.
     */
    public function testGetUsesDefaultWhenMissing(): void
    {
        $this->assertFalse(App::has('missing'));
        $this->assertSame('fallback', App::get('missing', 'fallback'));
    }

    /**
     * Resetting a specific key only clears that one service.
     */
    public function testResetClearsSpecificService(): void
    {
        App::set('foo', 'bar');
        App::set('baz', 'qux');
        App::reset('foo');

        $this->assertNull(App::get('foo'));
        $this->assertSame('qux', App::get('baz'));
    }

    /**
     * Reset without arguments should clear the entire registry.
     */
    public function testResetClearsAllServices(): void
    {
        App::set('foo', 'bar');
        App::set('baz', 'qux');
        App::reset();

        $this->assertNull(App::get('foo'));
        $this->assertNull(App::get('baz'));
    }

    /**
     * When nothing is registered, App helpers should still read legacy globals.
     */
    public function testFallbackReadsLegacyGlobals(): void
    {
        $GLOBALS['config'] = ['foo' => 'bar'];
        $GLOBALS['db'] = new \stdClass();

        $this->assertSame('bar', App::config()['foo']);
        $this->assertInstanceOf(\stdClass::class, App::db());
    }
}
