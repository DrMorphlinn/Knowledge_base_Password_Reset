<?php
/**
 * Admin Dashboard
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn() || !hasRole('admin')) {
    setFlashMessage('danger', 'Access denied. Admins only.');
    redirect(BASE_URL . '/index.php');
}

$pageTitle = 'Admin Dashboard';
$pdo = getConnection();

// Stats
$totalUsers     = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalTeachers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
$totalStudents  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalLessons   = $pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
$pendingLessons = $pdo->query("SELECT COUNT(*) FROM lessons WHERE status='pending'")->fetchColumn();
$totalComments  = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-shield-lock"></i> Admin Dashboard</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= BASE_URL ?>/admin/manage-categories.php" class="btn btn-success">
            <i class="bi bi-folder-plus"></i> Manage Categories
        </a>
        <a href="<?= BASE_URL ?>/admin/manage-users.php" class="btn btn-info">
            <i class="bi bi-people"></i> Manage Users
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= e($totalUsers) ?></div>
                    <div>Total Users</div>
                </div>
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-info">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= e($totalTeachers) ?></div>
                    <div>Teachers</div>
                </div>
                <i class="bi bi-person-workspace"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= e($totalStudents) ?></div>
                    <div>Students</div>
                </div>
                <i class="bi bi-mortarboard"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-secondary">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= e($totalLessons) ?></div>
                    <div>Total Lessons</div>
                </div>
                <i class="bi bi-book"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
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
    <div class="col-md-3">
        <div class="stat-card bg-danger">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= e($totalComments) ?></div>
                    <div>Comments</div>
                </div>
                <i class="bi bi-chat-dots"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-dark">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= e($totalCategories) ?></div>
                    <div>Categories</div>
                </div>
                <i class="bi bi-folder"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <i class="bi bi-check-circle" style="font-size: 3rem; color: var(--bs-success);"></i>
                <h5 class="mt-2">Approve Lessons</h5>
                <p><?= e($pendingLessons) ?> lessons pending review</p>
                <a href="<?= BASE_URL ?>/admin/manage-lessons.php" class="btn btn-success">
                    <i class="bi bi-check-all"></i> Review Now
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <i class="bi bi-people" style="font-size: 3rem; color: var(--bs-info);"></i>
                <h5 class="mt-2">Manage Users</h5>
                <p><?= e($totalUsers) ?> registered users</p>
                <a href="<?= BASE_URL ?>/admin/manage-users.php" class="btn btn-info">
                    <i class="bi bi-person-gear"></i> Manage Users
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <i class="bi bi-folder" style="font-size: 3rem; color: var(--bs-warning);"></i>
                <h5 class="mt-2">Categories</h5>
                <p><?= e($totalCategories) ?> categories</p>
                <a href="<?= BASE_URL ?>/admin/manage-categories.php" class="btn btn-warning">
                    <i class="bi bi-gear"></i> Manage Categories
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
