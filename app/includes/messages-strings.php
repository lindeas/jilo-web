<?php

// Message strings for translation
return [
    'LOGIN' => [
        'LOGIN_SUCCESS' => 'Login successful.',
        'LOGIN_FAILED' => 'Login failed. Please check your credentials.',
        'LOGOUT_SUCCESS' => 'Logout successful. You can log in again.',
        'IP_BLACKLISTED' => 'Access denied. Your IP address is blacklisted.',
        'IP_NOT_WHITELISTED' => 'Access denied. Your IP address is not whitelisted.',
        'TOO_MANY_ATTEMPTS' => 'Too many login attempts. Please try again later.',
    ],
    'SECURITY' => [
        'WHITELIST_ADD_SUCCESS' => 'IP address successfully added to whitelist.',
        'WHITELIST_ADD_ERROR' => 'Failed to add IP to whitelist. Please check the IP format.',
        'WHITELIST_REMOVE_SUCCESS' => 'IP address successfully removed from whitelist.',
        'WHITELIST_REMOVE_ERROR' => 'Failed to remove IP from whitelist.',
        'BLACKLIST_ADD_SUCCESS' => 'IP address successfully added to blacklist.',
        'BLACKLIST_ADD_ERROR' => 'Failed to add IP to blacklist. Please check the IP format.',
        'BLACKLIST_REMOVE_SUCCESS' => 'IP address successfully removed from blacklist.',
        'BLACKLIST_REMOVE_ERROR' => 'Failed to remove IP from blacklist.',
        'RATE_LIMIT_INFO' => 'Rate limiting is active. This helps protect against brute force attacks.',
        'PERMISSION_DENIED' => 'Permission denied. You do not have the required rights.',
        'IP_REQUIRED' => 'IP address is required.',
    ],
    'REGISTER' => [
        'SUCCESS' => 'Registration successful. You can log in now.',
        'FAILED' => 'Registration failed: %s',
        'DISABLED' => 'Registration is disabled.',
    ],
    'SYSTEM' => [
        'DB_ERROR' => 'Error connecting to the database: %s',
        'DB_CONNECT_ERROR' => 'Error connecting to DB: %s',
        'DB_UNKNOWN_TYPE' => 'Error: unknown database type "%s"',
    ],
];
