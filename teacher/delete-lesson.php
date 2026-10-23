<?php
/**
 * Delete Lesson
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn() || !hasRole('teacher')) {
    setFlashMessage('danger', 'Access denied.');
    redirect(BASE_URL . '/index.php');
}

$pdo = getConnection();
$userId = $_SESSION['user_id'];
$lessonId = intval($_GET['id'] ?? 0);

if ($lessonId <= 0) {
    setFlashMessage('danger', 'Invalid lesson ID.');
    redirect(BASE_URL . '/teacher/dashboard.php');
}

// Fetch lesson (ensure ownership)
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ? AND user_id = ?");
$stmt->execute([$lessonId, $userId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    setFlashMessage('danger', 'Lesson not found or access denied.');
    redirect(BASE_URL . '/teacher/dashboard.php');
}

// Delete file if exists
if ($lesson['file_path'] && file_exists(__DIR__ . '/../' . $lesson['file_path'])) {
    unlink(__DIR__ . '/../' . $lesson['file_path']);
}

// Delete comments first, then lesson
$pdo->prepare("DELETE FROM comments WHERE lesson_id = ?")->execute([$lessonId]);
$pdo->prepare("DELETE FROM lessons WHERE id = ?")->execute([$lessonId]);

setFlashMessage('success', 'Lesson deleted successfully.');
redirect(BASE_URL . '/teacher/dashboard.php');

