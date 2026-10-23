<?php
/**
 * Helper Functions
 * Knowledge Base Portal
 */

/**
 * Safely escape output for HTML (XSS prevention)
 * @param string $data
 * @return string
 */
function e($data): string
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if logged-in user has a specific role
 * @param string $role
 * @return bool
 */
function hasRole(string $role): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Redirect to a URL
 * @param string $url
 */
function redirect(string $url): void
{
    header("Location: " . $url);
    exit;
}

/**
 * Display flash message from session
 */
function displayFlashMessage(): void
{
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        $type = $msg['type'] ?? 'info';
        $text = $msg['text'] ?? '';
        echo '<div class="alert alert-' . e($type) . ' alert-dismissible fade show" role="alert">';
        echo e($text);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['flash_message']);
    }
}

/**
 * Set a flash message
 * @param string $type
 * @param string $text
 */
function setFlashMessage(string $type, string $text): void
{
    $_SESSION['flash_message'] = ['type' => $type, 'text' => $text];
}

/**
 * Get list of categories from database
 * @return array
 */
function getCategories(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll();
}

/**
 * Generate a URL-friendly slug
 * @param string $string
 * @return string
 */
function createSlug(string $string): string
{
    $slug = preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim($string)));
    return trim($slug, '-');
}

