<?php
/**
 * Session Initialization & Auth Check
 * Knowledge Base Portal
 */

// Load config FIRST so session INI settings take effect before session_start()
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Start session after config is loaded
session_start();

// Auto-redirect to login if not authenticated (except for allowed pages)
$publicPages = ['/knowledge-base/auth/login.php', '/knowledge-base/auth/register.php', '/knowledge-base/index.php'];

$currentPage = $_SERVER['PHP_SELF'];
if (!isLoggedIn() && !in_array($currentPage, $publicPages) && $currentPage !== '/knowledge-base/index.php') {
    // Allow public access to specific pages
    $allowedPrefixes = ['/knowledge-base/lessons/view.php'];
    $allowed = false;
    foreach ($allowedPrefixes as $prefix) {
        if (strpos($currentPage, $prefix) === 0) {
            $allowed = true;
            break;
        }
    }
    if (!$allowed) {
        redirect(BASE_URL . '/auth/login.php');
    }
}

