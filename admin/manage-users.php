<?php
/**
 * Admin - Manage Users
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn() || !hasRole('admin')) {
    setFlashMessage('danger', 'Access denied.');
    redirect(BASE_URL . '/index.php');
}

$pageTitle = 'Manage Users';
$pdo = getConnection();

// Handle role change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['new_role'])) {
    $userId = intval($_POST['user_id']);
    $newRole = $_POST['new_role'];

    if (in_array($newRole, ['admin', 'teacher', 'student'])) {
        // Don't allow changing own role
        if ($userId !== $_SESSION['user_id']) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $userId]);
            setFlashMessage('success', 'User role updated successfully.');
        } else {
            setFlashMessage('danger', 'You cannot change your own role.');
        }
        redirect(BASE_URL . '/admin/manage-users.php');
    }
}

// Handle delete user
if (isset($_GET['delete']) && intval($_GET['delete']) > 0) {
    $deleteId = intval($_GET['delete']);
    if ($deleteId !== $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$deleteId]);
        setFlashMessage('success', 'User deleted successfully.');
    } else {
        setFlashMessage('danger', 'You cannot delete your own account.');
    }
    redirect(BASE_URL . '/admin/manage-users.php');
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-people"></i> Manage Users</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= e($user['name']) ?>
                                <?php if ($user['id'] === $_SESSION['user_id']): ?>
                                    <span class="badge bg-primary">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($user['email']) ?></td>
                            <td>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
                                    <select name="new_role" class="form-select form-select-sm d-inline w-auto"
                                            onchange="this.form.submit()"
                                        <?= $user['id'] === $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                        <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                    </select>
                                    <?php if ($user['id'] === $_SESSION['user_id']): ?>
                                        <input type="hidden" name="new_role" value="<?= e($user['role']) ?>">
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td><?= e(date('M j, Y', strtotime($user['created_at']))) ?></td>
                            <td>
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <a href="?delete=<?= e($user['id']) ?>"
                                       class="btn btn-sm btn-danger"
                                       data-confirm="Delete user '<?= e($user['name']) ?>' and all their data?"
                                       title="Delete User">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
