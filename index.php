<?php
/**
 * Homepage
 * Knowledge Base Portal
 */
require_once __DIR__ . '/includes/session.php';

$pageTitle = 'Home';
$pdo = getConnection();

// Get stats
$totalLessons = $pdo->query("SELECT COUNT(*) FROM lessons WHERE status='approved'")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();

// Get recent approved lessons
$stmt = $pdo->query("
    SELECT l.*, c.name AS category_name, u.name AS teacher_name
    FROM lessons l
    LEFT JOIN categories c ON l.category_id = c.id
    LEFT JOIN users u ON l.user_id = u.id
    WHERE l.status = 'approved'
    ORDER BY l.created_at DESC
    LIMIT 6
");
$recentLessons = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section text-center mb-5">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome to Knowledge Base</h1>
        <p class="lead mb-4">Discover, learn, and share knowledge through video lessons and PDF resources.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>/lessons/index.php" class="btn btn-light btn-lg px-4">
                <i class="bi bi-collection-play"></i> Browse Lessons
            </a>
            <?php if (!isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-outline-light btn-lg px-4">
                    <i class="bi bi-person-plus"></i> Get Started
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Stats Section -->
<div class="container mb-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="stat-card bg-primary text-center">
                <i class="bi bi-book"></i>
                <div class="stat-number"><?= e($totalLessons) ?></div>
                <div>Lessons</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-success text-center">
                <i class="bi bi-folder"></i>
                <div class="stat-number"><?= e($totalCategories) ?></div>
                <div>Categories</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-info text-center">
                <i class="bi bi-people"></i>
                <div class="stat-number"><?= e($totalTeachers) ?></div>
                <div>Teachers</div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Lessons -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-clock-history"></i> Recent Lessons</h3>
        <a href="<?= BASE_URL ?>/lessons/index.php" class="btn btn-outline-primary">
            View All <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <?php if (count($recentLessons) > 0): ?>
        <div class="row g-4">
            <?php foreach ($recentLessons as $lesson): ?>
                <div class="col-md-4">
                    <div class="card h-100 lesson-card">
                        <?php if ($lesson['type'] === 'video'): ?>
                            <div class="card-img-top bg-dark d-flex align-items-center justify-content-center"
                                 style="height: 200px;">
                                <i class="bi bi-play-circle text-white" style="font-size: 4rem;"></i>
                            </div>
                        <?php else: ?>
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center"
                                 style="height: 200px;">
                                <i class="bi bi-file-pdf text-white" style="font-size: 4rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= e($lesson['title']) ?></h5>
                            <p class="card-text text-muted small">
                                <?= e(substr($lesson['description'] ?? '', 0, 100)) ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-<?= $lesson['type'] === 'video' ? 'danger' : 'secondary' ?>">
                                    <?= e($lesson['type']) ?>
                                </span>
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> <?= e($lesson['teacher_name'] ?? 'Unknown') ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="<?= BASE_URL ?>/lessons/view.php?id=<?= e($lesson['id']) ?>"
                               class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-eye"></i> View Lesson
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No lessons available yet. Check back later!
        </div>
    <?php endif; ?>
</div>

<!-- Categories Quick Links -->
<div class="container mb-5">
    <h3 class="mb-4"><i class="bi bi-grid"></i> Browse by Category</h3>
    <div class="row g-3">
        <?php
        $categories = getCategories($pdo);
        foreach ($categories as $cat):
        ?>
            <div class="col-md-3 col-6">
                <a href="<?= BASE_URL ?>/lessons/index.php?category=<?= e($cat['slug']) ?>"
                   class="text-decoration-none">
                    <div class="card text-center p-3">
                        <i class="bi bi-folder2-open" style="font-size: 2rem;"></i>
                        <h6 class="mt-2"><?= e($cat['name']) ?></h6>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
