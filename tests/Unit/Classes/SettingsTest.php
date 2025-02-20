<?php

require_once dirname(__DIR__, 3) . '/app/classes/database.php';
require_once dirname(__DIR__, 3) . '/app/classes/settings.php';

use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    private $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settings = new Settings(null);
    }

    public function testGetPlatformJsFileWithInvalidUrl()
    {
        $result = $this->settings->getPlatformJsFile('invalid-url', 'test.js');
        $this->assertEquals('Invalid URL: invalid-url/test.js', $result);
    }

    public function testGetPlatformJsFileWithValidUrl()
    {
        $result = $this->settings->getPlatformJsFile('https://example.com', 'test.js');
        $this->assertStringContainsString("can't be loaded", $result);
    }
}
