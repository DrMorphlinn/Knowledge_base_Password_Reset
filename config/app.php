<?php
/**
 * Application Configuration
 * Knowledge Base Portal
 */

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Base URL (change in production)
define('BASE_URL', 'http://localhost/knowledge-base');
define('UPLOAD_PATH', __DIR__ . '/../uploads');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

