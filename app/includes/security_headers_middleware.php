<?php

/**
 * Security Headers Middleware
 * 
 * Sets various security headers to protect against common web vulnerabilities:
 * - HSTS: Force HTTPS connections
 * - CSP: Content Security Policy to prevent XSS and other injection attacks
 * - X-Frame-Options: Prevent clickjacking
 * - X-Content-Type-Options: Prevent MIME-type sniffing
 * - Referrer-Policy: Control referrer information
 * - Permissions-Policy: Control browser features
 */

// Get current page
$current_page = $_GET['page'] ?? 'dashboard';

// Define pages that need media access
$media_enabled_pages = [
    // 'conference' => ['camera', 'microphone'],
    // 'call' => ['microphone'],
    // Add more pages and their required permissions as needed
];

// Strict Transport Security (HSTS)
// Only enable if HTTPS is properly configured
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Content Security Policy (CSP)
$csp = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Required for Bootstrap and jQuery
    "style-src 'self' 'unsafe-inline' https://use.fontawesome.com", // Allow FontAwesome CSS
    "img-src 'self' data:", // Allow data: URLs for images
    "font-src 'self' https://use.fontawesome.com", // Allow FontAwesome fonts
    "connect-src 'self'",
    "frame-ancestors 'none'", // Equivalent to X-Frame-Options: DENY
    "form-action 'self'",
    "base-uri 'self'",
    "upgrade-insecure-requests" // Force HTTPS for all requests
];
header("Content-Security-Policy: " . implode('; ', $csp));

// X-Frame-Options (legacy support)
header('X-Frame-Options: DENY');

// X-Content-Type-Options
header('X-Content-Type-Options: nosniff');

// Referrer-Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Permissions-Policy
$permissions = [
    'geolocation=()',
    'payment=()',
    'usb=()',
    'accelerometer=()',
    'autoplay=()',
    'document-domain=()',
    'encrypted-media=()',
    'fullscreen=(self)',
    'magnetometer=()',
    'midi=()',
    'sync-xhr=(self)',
    'usb=()'
];

// Add camera/microphone permissions based on current page
$camera_allowed = false;
$microphone_allowed = false;

if (isset($media_enabled_pages[$current_page])) {
    $allowed_media = $media_enabled_pages[$current_page];
    if (in_array('camera', $allowed_media)) {
        $camera_allowed = true;
    }
    if (in_array('microphone', $allowed_media)) {
        $microphone_allowed = true;
    }
}

// Add media permissions
$permissions[] = $camera_allowed ? 'camera=(self)' : 'camera=()';
$permissions[] = $microphone_allowed ? 'microphone=(self)' : 'microphone=()';

header('Permissions-Policy: ' . implode(', ', $permissions));

// Clear PHP version
header_remove('X-Powered-By');

// Prevent caching of sensitive pages
if (in_array($current_page, ['login', 'register', 'profile', 'security'])) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT');
}
