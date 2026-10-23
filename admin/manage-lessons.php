<?php
/**
 * Admin - Manage Lessons (Approve/Reject)
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn() || !hasRole('admin')) {
    setFlashMessage('danger', 'Access denied.');
    redirect(BASE_URL . '/index.php');
}

$pageTitle = 'Manage Lessons';
$pdo = getConnection();

// Handle status change (approve/reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lesson_id']) && isset($_POST['action'])) {
    $lessonId = intval($_POST['lesson_id']);
    $action   = $_POST['action'];

    if (in_array($action, ['approve', 'reject'])) {
        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
        $stmt = $pdo->prepare("UPDATE lessons SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $lessonId]);
        setFlashMessage('success', 'Lesson ' . $newStatus . ' successfully.');
        redirect(BASE_URL . '/admin/manage-lessons.php');
    }
}

// Filter
$filter = $_GET['filter'] ?? 'pending';

$where = "1=1";
if ($filter === 'pending') {
    $where = "l.status = 'pending'";
} elseif ($filter === 'approved') {
    $where = "l.status = 'approved'";
} elseif ($filter === 'rejected') {
    $where = "l.status = 'rejected'";
} elseif ($filter === 'all') {
    $where = "1=1";
}

$stmt = $pdo->query("
    SELECT l.*, c.name AS category_name, u.name AS teacher_name, u.email AS teacher_email
    FROM lessons l
    LEFT JOIN categories c ON l.category_id = c.id
    LEFT JOIN users u ON l.user_id = u.id
    WHERE $where
    ORDER BY l.created_at DESC
");
$lessons = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-list-check"></i> Manage Lessons</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $filter === 'pending' ? 'active' : '' ?>" href="?filter=pending">
            Pending <span class="badge bg-warning text-dark">
                <?php
                $count = $pdo->query("SELECT COUNT(*) FROM lessons WHERE status='pending'")->fetchColumn();
                echo e($count);
                ?>
            </span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filter === 'approved' ? 'active' : '' ?>" href="?filter=approved">Approved</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filter === 'rejected' ? 'active' : '' ?>" href="?filter=rejected">Rejected</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filter === 'all' ? 'active' : '' ?>" href="?filter=all">All</a>
    </li>
</ul>

<!-- Lessons Table -->
<?php if (count($lessons) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Teacher</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lessons as $index => $lesson): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= e($lesson['title']) ?></td>
                        <td>
                            <?= e($lesson['teacher_name'] ?? 'Unknown') ?>
                            <br><small class="text-muted"><?= e($lesson['teacher_email'] ?? '') ?></small>
                        </td>
                        <td>
                            <span class="badge bg-<?= $lesson['type'] === 'video' ? 'danger' : 'secondary' ?>">
                                <?= e($lesson['type']) ?>
                            </span>
                        </td>
                        <td><?= e($lesson['category_name'] ?? '-') ?></td>
                        <td>
                            <?php if ($lesson['status'] === 'approved'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php elseif ($lesson['status'] === 'rejected'): ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e(date('M j, Y', strtotime($lesson['created_at']))) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/lessons/view.php?id=<?= e($lesson['id']) ?>"
                               class="btn btn-sm btn-info" title="Preview">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($lesson['status'] === 'pending'): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="lesson_id" value="<?= e($lesson['id']) ?>">
                                    <button type="submit" name="action" value="approve"
                                            class="btn btn-sm btn-success" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button type="submit" name="action" value="reject"
                                            class="btn btn-sm btn-danger" title="Reject">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <!-- Delete button for admin -->
                            <a href="<?= BASE_URL ?>/admin/delete-lesson.php?id=<?= e($lesson['id']) ?>"
                               class="btn btn-sm btn-outline-danger"
                               data-confirm="Delete this lesson permanently?"
                               title="Delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No lessons found in this category.
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
