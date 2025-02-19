<?php

require_once dirname(__DIR__, 4) . '/app/classes/feedback.php';

use PHPUnit\Framework\TestCase;

class FeedbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Start session for flash messages
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        // Initialize session variables
        $_SESSION = [];
        $_SESSION['flash_messages'] = [];
    }

    protected function tearDown(): void
    {
        // Clean up session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        parent::tearDown();
    }

    public function testGetFlash()
    {
        // Add a test message
        Feedback::flash('LOGIN', 'LOGIN_SUCCESS', 'Test message');
        $messages = $_SESSION['flash_messages'];

        $this->assertIsArray($messages);
        $this->assertCount(1, $messages);

        $message = $messages[0];
        $this->assertEquals('LOGIN', $message['category']);
        $this->assertEquals('LOGIN_SUCCESS', $message['key']);
        $this->assertEquals('Test message', $message['custom_message']);
    }

    public function testRender()
    {
        // Test success message with custom text
        $output = Feedback::render('LOGIN', 'LOGIN_SUCCESS', 'Success message');
        $this->assertStringContainsString('alert-success', $output);
        $this->assertStringContainsString('Success message', $output);
        $this->assertStringContainsString('alert-dismissible', $output);

        // Test error message (non-dismissible)
        $output = Feedback::render('LOGIN', 'LOGIN_FAILED', 'Error message');
        $this->assertStringContainsString('alert-danger', $output);
        $this->assertStringContainsString('Error message', $output);
        $this->assertStringNotContainsString('alert-dismissible', $output);

        // Test small message
        $output = Feedback::render('LOGIN', 'LOGIN_SUCCESS', 'Small message', true, true);
        $this->assertStringContainsString('alert-sm', $output);
        $this->assertStringContainsString('btn-close-sm', $output);
    }

    public function testGetMessageData()
    {
        $data = Feedback::getMessageData('LOGIN', 'LOGIN_SUCCESS', 'Test message');

        $this->assertIsArray($data);
        $this->assertEquals(Feedback::TYPE_SUCCESS, $data['type']);
        $this->assertEquals('Test message', $data['message']);
        $this->assertTrue($data['dismissible']);
        $this->assertFalse($data['small']);

        // Test with default message
        $data = Feedback::getMessageData('LOGIN', 'LOGIN_SUCCESS');
        $this->assertNotNull($data['message']);
    }

    public function testFlash()
    {
        Feedback::flash('LOGIN', 'LOGIN_SUCCESS', 'Test message');
        $this->assertArrayHasKey('flash_messages', $_SESSION);
        $this->assertCount(1, $_SESSION['flash_messages']);

        $message = $_SESSION['flash_messages'][0];
        $this->assertEquals('LOGIN', $message['category']);
        $this->assertEquals('LOGIN_SUCCESS', $message['key']);
        $this->assertEquals('Test message', $message['custom_message']);
    }

    public function testPredefinedMessageTypes()
    {
        $this->assertEquals('success', Feedback::TYPE_SUCCESS);
        $this->assertEquals('danger', Feedback::TYPE_ERROR);
        $this->assertEquals('info', Feedback::TYPE_INFO);
        $this->assertEquals('warning', Feedback::TYPE_WARNING);
    }

    public function testMessageConfigurations()
    {
        $config = Feedback::get('LOGIN', 'LOGIN_SUCCESS');
        $this->assertEquals(Feedback::TYPE_SUCCESS, $config['type']);
        $this->assertTrue($config['dismissible']);

        $config = Feedback::get('LOGIN', 'LOGIN_FAILED');
        $this->assertEquals(Feedback::TYPE_ERROR, $config['type']);
        $this->assertFalse($config['dismissible']);
    }
}
