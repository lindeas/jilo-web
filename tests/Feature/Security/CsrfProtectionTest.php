<?php

namespace Tests\Framework\Integration\Security;

require_once dirname(__DIR__, 3) . '/app/classes/log.php';
require_once dirname(__DIR__, 3) . '/app/helpers/security.php';

use PHPUnit\Framework\TestCase;

class TestLogger {
    public static function insertLog($userId, $message, $scope = 'user') {
        return true;
    }
}

// Override the CSRF middleware to use our test logger
function applyCsrfMiddleware() {
    $security = \SecurityHelper::getInstance();

    // Skip CSRF check for GET requests
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return ['status' => 200, 'message' => ''];
    }

    // Skip CSRF check for initial login attempt
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
        isset($_GET['page']) && $_GET['page'] === 'login' && 
        !isset($_SESSION['username'])) {
        return ['status' => 200, 'message' => ''];
    }

    // Check CSRF token for all other POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!$security->verifyCsrfToken($token)) {
            // Log CSRF attempt
            $message = "CSRF attempt detected from IP: " . $_SERVER['REMOTE_ADDR'];
            TestLogger::insertLog(0, $message, 'system');

            // Return error message
            return ['status' => 403, 'message' => $message];
        }
    }

    return ['status' => 200, 'message' => ''];
}

class CsrfProtectionTest extends TestCase
{
    private $security;

    protected function setUp(): void
    {
        parent::setUp();
        $this->security = \SecurityHelper::getInstance();
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($_SESSION['csrf_token']);
        unset($_POST['csrf_token']);
        unset($_GET['page']);
        unset($_SESSION['username']);
    }

    public function testCsrfProtectionValidToken()
    {
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $token = $_SESSION['csrf_token'];

        // Simulate POST request with valid token
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = $token;

        // Call CSRF middleware
        $response = applyCsrfMiddleware();

        // Assert that the response is success
        $this->assertEquals(200, $response['status']);
        $this->assertEmpty($response['message']);
    }

    public function testCsrfProtectionInvalidToken()
    {
        // Simulate POST request with invalid token
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = 'invalid_token';

        // Call CSRF middleware
        $response = applyCsrfMiddleware();

        // Assert that the response is forbidden with error message
        $this->assertEquals(403, $response['status']);
        $this->assertStringContainsString("CSRF attempt detected from IP", $response['message']);
    }

    public function testCsrfProtectionGetRequest()
    {
        // Simulate GET request without token
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Call CSRF middleware
        $response = applyCsrfMiddleware();

        // Assert that GET requests are allowed without token
        $this->assertEquals(200, $response['status']);
        $this->assertEmpty($response['message']);
    }

    public function testCsrfProtectionInitialLogin()
    {
        // Simulate initial login POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page'] = 'login';

        // Call CSRF middleware
        $response = applyCsrfMiddleware();

        // Assert that initial login is allowed without token
        $this->assertEquals(200, $response['status']);
        $this->assertEmpty($response['message']);
    }

    public function testCsrfProtectionMissingToken()
    {
        // Simulate POST request without token
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Call CSRF middleware
        $response = applyCsrfMiddleware();

        // Assert that missing token is rejected
        $this->assertEquals(403, $response['status']);
        $this->assertStringContainsString("CSRF attempt detected from IP", $response['message']);
    }

    public function testCsrfProtectionEmptyToken()
    {
        // Simulate POST request with empty token
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = '';

        // Call CSRF middleware
        $response = applyCsrfMiddleware();

        // Assert that empty token is rejected
        $this->assertEquals(403, $response['status']);
        $this->assertStringContainsString("CSRF attempt detected from IP", $response['message']);
    }
}
