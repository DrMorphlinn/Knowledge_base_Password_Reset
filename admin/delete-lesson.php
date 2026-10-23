<?php
/**
 * Admin - Delete Lesson (full access)
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn() || !hasRole('admin')) {
    setFlashMessage('danger', 'Access denied.');
    redirect(BASE_URL . '/index.php');
}

$pdo = getConnection();
$lessonId = intval($_GET['id'] ?? 0);

if ($lessonId <= 0) {
    setFlashMessage('danger', 'Invalid lesson ID.');
    redirect(BASE_URL . '/admin/manage-lessons.php');
}

// Fetch lesson
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    setFlashMessage('danger', 'Lesson not found.');
    redirect(BASE_URL . '/admin/manage-lessons.php');
}

// Delete file if exists
if ($lesson['file_path'] && file_exists(__DIR__ . '/../' . $lesson['file_path'])) {
    unlink(__DIR__ . '/../' . $lesson['file_path']);
}

// Delete comments then lesson
$pdo->prepare("DELETE FROM comments WHERE lesson_id = ?")->execute([$lessonId]);
$pdo->prepare("DELETE FROM lessons WHERE id = ?")->execute([$lessonId]);

setFlashMessage('success', 'Lesson deleted permanently.');
redirect(BASE_URL . '/admin/manage-lessons.php');

