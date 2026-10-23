<?php
/**
 * Lessons Listing
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

$pageTitle = 'Lessons';
$pdo = getConnection();

// Build query with filters
$where = "WHERE l.status = 'approved'";
$params = [];

// Category filter
if (!empty($_GET['category'])) {
    $where .= " AND c.slug = ?";
    $params[] = $_GET['category'];
}

// Search filter
if (!empty($_GET['search'])) {
    $where .= " AND (l.title LIKE ? OR l.description LIKE ?)";
    $searchTerm = '%' . $_GET['search'] . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Type filter
if (!empty($_GET['type']) && in_array($_GET['type'], ['video', 'pdf'])) {
    $where .= " AND l.type = ?";
    $params[] = $_GET['type'];
}

// Get total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM lessons l LEFT JOIN categories c ON l.category_id = c.id $where");
$countStmt->execute($params);
$totalLessons = $countStmt->fetchColumn();

// Pagination
$perPage = 9;
$totalPages = ceil($totalLessons / $perPage);
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Fetch lessons
$stmt = $pdo->prepare("
    SELECT l.*, c.name AS category_name, u.name AS teacher_name
    FROM lessons l
    LEFT JOIN categories c ON l.category_id = c.id
    LEFT JOIN users u ON l.user_id = u.id
    $where
    ORDER BY l.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$lessons = $stmt->fetchAll();

// Get all categories for filter dropdown
$categories = getCategories($pdo);

include __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-collection-play"></i> Browse Lessons</h2>
    </div>
    <div class="col-md-4">
        <form method="GET" action="" class="d-flex">
            <input type="text" class="form-control me-2" name="search"
                   placeholder="Search lessons..." value="<?= e($_GET['search'] ?? '') ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <strong>Filter:</strong>
                    </div>
                    <div class="col-auto">
                        <a href="<?= BASE_URL ?>/lessons/index.php"
                           class="btn btn-sm <?= empty($_GET['category']) && empty($_GET['type']) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                            All
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="<?= BASE_URL ?>/lessons/index.php?type=video<?= !empty($_GET['category']) ? '&category=' . e($_GET['category']) : '' ?>"
                           class="btn btn-sm <?= ($_GET['type'] ?? '') === 'video' ? 'btn-danger' : 'btn-outline-danger' ?>">
                            <i class="bi bi-play-circle"></i> Videos
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="<?= BASE_URL ?>/lessons/index.php?type=pdf<?= !empty($_GET['category']) ? '&category=' . e($_GET['category']) : '' ?>"
                           class="btn btn-sm <?= ($_GET['type'] ?? '') === 'pdf' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                            <i class="bi bi-file-pdf"></i> PDFs
                        </a>
                    </div>
                    <div class="col-auto">
                        <span class="text-muted">|</span>
                    </div>
                    <?php foreach ($categories as $cat): ?>
                        <div class="col-auto">
                            <a href="<?= BASE_URL ?>/lessons/index.php?category=<?= e($cat['slug']) ?><?= !empty($_GET['type']) ? '&type=' . e($_GET['type']) : '' ?>"
                               class="btn btn-sm <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'btn-success' : 'btn-outline-success' ?>">
                                <?= e($cat['name']) ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lessons Grid -->
<?php if (count($lessons) > 0): ?>
    <div class="row g-4">
        <?php foreach ($lessons as $lesson): ?>
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
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-<?= $lesson['type'] === 'video' ? 'danger' : 'secondary' ?>">
                                <?= e($lesson['type']) ?>
                            </span>
                            <?php if ($lesson['category_name']): ?>
                                <span class="badge bg-success"><?= e($lesson['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h5 class="card-title"><?= e($lesson['title']) ?></h5>
                        <p class="card-text text-muted small">
                            <?= e(substr($lesson['description'] ?? '', 0, 120)) ?>...
                        </p>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-person"></i> <?= e($lesson['teacher_name'] ?? 'Unknown') ?>
                        </small>
                        <a href="<?= BASE_URL ?>/lessons/view.php?id=<?= e($lesson['id']) ?>"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= !empty($_GET['category']) ? '&category=' . e($_GET['category']) : '' ?><?= !empty($_GET['type']) ? '&type=' . e($_GET['type']) : '' ?><?= !empty($_GET['search']) ? '&search=' . e($_GET['search']) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No lessons found matching your criteria.
        <a href="<?= BASE_URL ?>/lessons/index.php" class="alert-link">Clear filters</a>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
