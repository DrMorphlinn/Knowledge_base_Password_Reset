<?php
/**
 * Admin - Manage Categories (CRUD)
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn() || !hasRole('admin')) {
    setFlashMessage('danger', 'Access denied.');
    redirect(BASE_URL . '/index.php');
}

$pageTitle = 'Manage Categories';
$pdo = getConnection();

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $name = trim($_POST['name'] ?? '');
    if (!empty($name)) {
        $slug = createSlug($name);
        // Check uniqueness
        $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetch()) {
            $slug = $slug . '-' . time();
        }
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        setFlashMessage('success', 'Category created successfully.');
    } else {
        setFlashMessage('danger', 'Category name is required.');
    }
    redirect(BASE_URL . '/admin/manage-categories.php');
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit']) && isset($_POST['category_id'])) {
    $catId = intval($_POST['category_id']);
    $name = trim($_POST['name'] ?? '');
    if (!empty($name) && $catId > 0) {
        $slug = createSlug($name);
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $catId]);
        setFlashMessage('success', 'Category updated successfully.');
    } else {
        setFlashMessage('danger', 'Category name is required.');
    }
    redirect(BASE_URL . '/admin/manage-categories.php');
}

// Handle delete
if (isset($_GET['delete']) && intval($_GET['delete']) > 0) {
    $catId = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$catId]);
    setFlashMessage('success', 'Category deleted successfully.');
    redirect(BASE_URL . '/admin/manage-categories.php');
}

// Fetch all categories with lesson count
$stmt = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM lessons l WHERE l.category_id = c.id) AS lesson_count
    FROM categories c
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-folder"></i> Manage Categories</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <!-- Create Category Form -->
    <div class="col-md-4 mb-4">
        <div class="card shadow">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-plus-circle"></i> Add Category</h5>
                <hr>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               placeholder="e.g. Web Development">
                    </div>
                    <button type="submit" name="create" class="btn btn-success w-100">
                        <i class="bi bi-plus"></i> Create Category
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-list"></i> All Categories</h5>
                <hr>
                <?php if (count($categories) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Lessons</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $index => $cat): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <!-- Inline edit form -->
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="category_id" value="<?= e($cat['id']) ?>">
                                                <input type="text" name="name" value="<?= e($cat['name']) ?>"
                                                       class="form-control form-control-sm d-inline w-auto"
                                                       required>
                                                <button type="submit" name="edit" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td><code><?= e($cat['slug']) ?></code></td>
                                        <td><span class="badge bg-info"><?= e($cat['lesson_count']) ?></span></td>
                                        <td><?= e(date('M j, Y', strtotime($cat['created_at']))) ?></td>
                                        <td>
                                            <a href="?delete=<?= e($cat['id']) ?>"
                                               class="btn btn-sm btn-danger"
                                               data-confirm="Delete category '<?= e($cat['name']) ?>'? Lessons in this category will become uncategorized."
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
                    <div class="alert alert-info">No categories created yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
