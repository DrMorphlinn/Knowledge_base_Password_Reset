<?php
/**
 * Teacher Dashboard
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

// Restrict to teacher role only
if (!isLoggedIn() || !hasRole('teacher')) {
    setFlashMessage('danger', 'Access denied. Teachers only.');
    redirect(BASE_URL . '/index.php');
}

$pageTitle = 'Teacher Dashboard';
$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Get teacher stats
$totalLessons = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE user_id = ?");
$totalLessons->execute([$userId]);
$totalLessons = $totalLessons->fetchColumn();

$pendingLessons = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE user_id = ? AND status = 'pending'");
$pendingLessons->execute([$userId]);
$pendingLessons = $pendingLessons->fetchColumn();

$approvedLessons = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE user_id = ? AND status = 'approved'");
$approvedLessons->execute([$userId]);
$approvedLessons = $approvedLessons->fetchColumn();

// Get teacher's lessons
$stmt = $pdo->prepare("
    SELECT l.*, c.name AS category_name
    FROM lessons l
    LEFT JOIN categories c ON l.category_id = c.id
    WHERE l.user_id = ?
    ORDER BY l.created_at DESC
");
$stmt->execute([$userId]);
$myLessons = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-person-workspace"></i> Teacher Dashboard</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= BASE_URL ?>/teacher/create-lesson.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Lesson
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card bg-primary">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= e($totalLessons) ?></div>
                    <div>Total Lessons</div>
                </div>
                <i class="bi bi-book"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card bg-warning text-dark">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= e($pendingLessons) ?></div>
                    <div>Pending Approval</div>
                </div>
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card bg-success">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= e($approvedLessons) ?></div>
                    <div>Approved</div>
                </div>
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- My Lessons Table -->
<div class="card shadow">
    <div class="card-body">
        <h5 class="card-title"><i class="bi bi-list"></i> My Lessons</h5>
        <hr>

        <?php if (count($myLessons) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myLessons as $index => $lesson): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= e($lesson['title']) ?></td>
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
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/teacher/edit-lesson.php?id=<?= e($lesson['id']) ?>"
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/teacher/delete-lesson.php?id=<?= e($lesson['id']) ?>"
                                       class="btn btn-sm btn-danger"
                                       data-confirm="Delete this lesson? This cannot be undone."
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
                <i class="bi bi-info-circle"></i> You haven't created any lessons yet.
                <a href="<?= BASE_URL ?>/teacher/create-lesson.php" class="alert-link">Create your first lesson</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
