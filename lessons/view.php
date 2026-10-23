<?php
/**
 * Single Lesson View
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

$pageTitle = 'View Lesson';
$pdo = getConnection();

$lessonId = intval($_GET['id'] ?? 0);
if ($lessonId <= 0) {
    redirect(BASE_URL . '/lessons/index.php');
}

// Fetch lesson
$stmt = $pdo->prepare("
    SELECT l.*, c.name AS category_name, u.name AS teacher_name
    FROM lessons l
    LEFT JOIN categories c ON l.category_id = c.id
    LEFT JOIN users u ON l.user_id = u.id
    WHERE l.id = ?
");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    setFlashMessage('danger', 'Lesson not found.');
    redirect(BASE_URL . '/lessons/index.php');
}

// Only approved lessons are visible to students/guests
if ($lesson['status'] !== 'approved' && (!isLoggedIn() || $_SESSION['user_role'] === 'student')) {
    setFlashMessage('danger', 'This lesson is not available.');
    redirect(BASE_URL . '/lessons/index.php');
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment']) && isLoggedIn()) {
    $commentText = trim($_POST['comment_text'] ?? '');

    if (!empty($commentText)) {
        $stmt = $pdo->prepare("INSERT INTO comments (lesson_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmt->execute([$lessonId, $_SESSION['user_id'], $commentText]);
        setFlashMessage('success', 'Comment added successfully.');
        redirect(BASE_URL . '/lessons/view.php?id=' . $lessonId);
    } else {
        setFlashMessage('danger', 'Comment cannot be empty.');
    }
}

// Fetch comments
$stmt = $pdo->prepare("
    SELECT c.*, u.name AS commenter_name, u.role AS commenter_role
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.lesson_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$lessonId]);
$comments = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Lesson Title & Meta -->
        <h2><?= e($lesson['title']) ?></h2>
        <div class="mb-3">
            <span class="badge bg-<?= $lesson['type'] === 'video' ? 'danger' : 'secondary' ?>">
                <?= e($lesson['type']) ?>
            </span>
            <?php if ($lesson['category_name']): ?>
                <span class="badge bg-success"><?= e($lesson['category_name']) ?></span>
            <?php endif; ?>
            <span class="badge bg-info">
                <i class="bi bi-person"></i> <?= e($lesson['teacher_name'] ?? 'Unknown') ?>
            </span>
            <span class="badge bg-secondary">
                <i class="bi bi-calendar"></i> <?= e(date('M j, Y', strtotime($lesson['created_at']))) ?>
            </span>
            <?php if ($lesson['status'] !== 'approved'): ?>
                <span class="badge bg-warning text-dark">
                    <i class="bi bi-exclamation-triangle"></i> <?= ucfirst(e($lesson['status'])) ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- Video or PDF Display -->
        <?php if ($lesson['type'] === 'video'): ?>
            <?php if (!empty($lesson['youtube_url'])): ?>
                <div class="video-container mb-4">
                    <?php
                    // Extract YouTube video ID
                    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $lesson['youtube_url'], $matches);
                    $videoId = $matches[1] ?? '';
                    ?>
                    <?php if ($videoId): ?>
                        <iframe src="https://www.youtube.com/embed/<?= e($videoId) ?>"
                                allowfullscreen></iframe>
                    <?php else: ?>
                        <a href="<?= e($lesson['youtube_url']) ?>" target="_blank" class="btn btn-danger">
                            <i class="bi bi-youtube"></i> Watch on YouTube
                        </a>
                    <?php endif; ?>
                </div>
            <?php elseif (!empty($lesson['file_path'])): ?>
                <div class="video-container mb-4">
                    <video controls>
                        <source src="<?= BASE_URL . '/' . e($lesson['file_path']) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No video source available.</div>
            <?php endif; ?>
        <?php elseif ($lesson['type'] === 'pdf'): ?>
            <?php if (!empty($lesson['file_path'])): ?>
                <div class="mb-3">
                    <a href="<?= BASE_URL . '/' . e($lesson['file_path']) ?>" class="btn btn-primary" target="_blank">
                        <i class="bi bi-download"></i> Download PDF
                    </a>
                    <a href="<?= BASE_URL . '/' . e($lesson['file_path']) ?>" class="btn btn-outline-secondary" target="_blank">
                        <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                    </a>
                </div>
                <iframe src="<?= BASE_URL . '/' . e($lesson['file_path']) ?>" class="pdf-container"></iframe>
            <?php else: ?>
                <div class="alert alert-warning">PDF file not available.</div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Description -->
        <div class="card mb-4">
            <div class="card-body">
                <h5><i class="bi bi-info-circle"></i> Description</h5>
                <p class="mb-0"><?= nl2br(e($lesson['description'] ?? 'No description provided.')) ?></p>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h5><i class="bi bi-chat-dots"></i> Comments (<?= count($comments) ?>)</h5>
                <hr>

                <?php if (isLoggedIn()): ?>
                    <form method="POST" action="" class="mb-4">
                        <div class="mb-3">
                            <textarea class="form-control" name="comment_text" rows="3"
                                      placeholder="Write a comment..." required></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn btn-primary">
                            <i class="bi bi-send"></i> Post Comment
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        <a href="<?= BASE_URL ?>/auth/login.php">Login</a> to post a comment.
                    </div>
                <?php endif; ?>

                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="d-flex mb-3 pb-3 border-bottom">
                            <div class="me-3">
                                <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                <strong><?= e($comment['commenter_name'] ?? 'Unknown') ?></strong>
                                <small class="text-muted ms-2">
                                    <?= e(date('M j, Y g:i A', strtotime($comment['created_at']))) ?>
                                </small>
                                <p class="mb-0 mt-1"><?= nl2br(e($comment['comment_text'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No comments yet. Be the first to comment!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5><i class="bi bi-info-square"></i> Lesson Info</h5>
                <ul class="list-unstyled">
                    <li><strong>Type:</strong> <?= ucfirst(e($lesson['type'])) ?></li>
                    <li><strong>Category:</strong> <?= e($lesson['category_name'] ?? 'Uncategorized') ?></li>
                    <li><strong>Teacher:</strong> <?= e($lesson['teacher_name'] ?? 'Unknown') ?></li>
                    <li><strong>Posted:</strong> <?= e(date('F j, Y', strtotime($lesson['created_at']))) ?></li>
                </ul>
            </div>
        </div>

        <!-- Back button -->
        <a href="<?= BASE_URL ?>/lessons/index.php" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left"></i> Back to Lessons
        </a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
