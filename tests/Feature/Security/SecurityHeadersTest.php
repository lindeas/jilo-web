<?php

namespace Tests\Framework\Integration\Security;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/app/includes/security_headers_middleware.php';

class SecurityHeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unset($_GET['page']);
        unset($_SERVER['HTTPS']);
        unset($_SERVER['REQUEST_URI']);
    }

    public function testBasicSecurityHeaders()
    {
        // Apply security headers in test mode
        $headers = \applySecurityHeaders(true);

        // Check security headers
        $this->assertContains('X-Frame-Options: DENY', $headers);
        $this->assertContains('X-XSS-Protection: 1; mode=block', $headers);
        $this->assertContains('X-Content-Type-Options: nosniff', $headers);
        $this->assertContains('Referrer-Policy: strict-origin-when-cross-origin', $headers);
    }

    public function testContentSecurityPolicy()
    {
        // Apply security headers in test mode
        $headers = \applySecurityHeaders(true);

        // Get CSP header
        $cspHeader = '';
        foreach ($headers as $header) {
            if (strpos($header, 'Content-Security-Policy:') === 0) {
                $cspHeader = $header;
                break;
            }
        }

        // Check CSP directives
        $this->assertStringContainsString("default-src 'self'", $cspHeader);
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' 'unsafe-eval'", $cspHeader);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline'", $cspHeader);
        $this->assertStringContainsString("frame-ancestors 'none'", $cspHeader);
        $this->assertStringContainsString("form-action 'self'", $cspHeader);
        $this->assertStringContainsString("base-uri 'self'", $cspHeader);
    }

    public function testHstsHeader()
    {
        // Simulate HTTPS
        $_SERVER['HTTPS'] = 'on';

        // Apply security headers in test mode
        $headers = \applySecurityHeaders(true);

        // Check HSTS header
        $this->assertContains(
            'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload',
            $headers
        );
    }

    public function testNoHstsHeaderOnHttp()
    {
        // Apply security headers in test mode
        $headers = \applySecurityHeaders(true);

        // Check HSTS header is not present
        $hasHsts = false;
        foreach ($headers as $header) {
            if (strpos($header, 'Strict-Transport-Security:') === 0) {
                $hasHsts = true;
                break;
            }
        }
        $this->assertFalse($hasHsts, 'HSTS header should not be present on HTTP');
    }

    public function testCacheControlForSensitivePages()
    {
        $sensitivePages = ['login', 'register', 'profile', 'security'];

        foreach ($sensitivePages as $page) {
            // Set current page
            $_GET['page'] = $page;

            // Apply security headers in test mode
            $headers = \applySecurityHeaders(true);

            // Check cache control headers
            $this->assertContains('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', $headers);
            $this->assertContains('Pragma: no-cache', $headers);
            $this->assertStringContainsString('Expires:', implode(' ', $headers));
        }
    }

    public function testNoCacheControlForNonSensitivePages()
    {
        $_GET['page'] = 'dashboard';

        // Apply security headers in test mode
        $headers = \applySecurityHeaders(true);

        // Check cache control headers are not present
        $this->assertNotContains('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', $headers);
        $this->assertNotContains('Pragma: no-cache', $headers);
    }

    public function testPermissionsPolicy()
    {
        // Apply security headers in test mode
        $headers = \applySecurityHeaders(true);

        // Get Permissions-Policy header
        $permissionsHeader = '';
        foreach ($headers as $header) {
            if (strpos($header, 'Permissions-Policy:') === 0) {
                $permissionsHeader = $header;
                break;
            }
        }

        // Check basic permissions
        $this->assertStringContainsString('geolocation=()', $permissionsHeader);
        $this->assertStringContainsString('payment=()', $permissionsHeader);
        $this->assertStringContainsString('camera=()', $permissionsHeader);
        $this->assertStringContainsString('microphone=()', $permissionsHeader);
        $this->assertStringContainsString('fullscreen=(self)', $permissionsHeader);
        $this->assertStringContainsString('sync-xhr=(self)', $permissionsHeader);
    }

    public function testPermissionsPolicyForMediaEnabledPages()
    {
        $_SERVER['REQUEST_URI'] = '/media/upload';

        // Apply security headers in test mode
        $headers = \applySecurityHeaders(true);

        // Get Permissions-Policy header
        $permissionsHeader = '';
        foreach ($headers as $header) {
            if (strpos($header, 'Permissions-Policy:') === 0) {
                $permissionsHeader = $header;
                break;
            }
        }

        // Check permissions policy header
        $this->assertStringContainsString('camera=()', $permissionsHeader);
        $this->assertStringContainsString('microphone=()', $permissionsHeader);
        $this->assertStringContainsString('geolocation=()', $permissionsHeader);
        $this->assertStringContainsString('payment=()', $permissionsHeader);
    }
}
