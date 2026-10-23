<?php
/**
 * User Profile
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = 'My Profile';
$pdo = getConnection();

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        $error = 'Name is required.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$name, $_SESSION['user_id']]);
        $_SESSION['user_name'] = $name;
        $success = 'Profile updated successfully.';
        $user['name'] = $name;
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current    = $_POST['current_password'] ?? '';
    $newPass    = $_POST['new_password'] ?? '';
    $confirmNew = $_POST['confirm_new_password'] ?? '';

    if (empty($current) || empty($newPass) || empty($confirmNew)) {
        $error = 'All password fields are required.';
    } elseif (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPass) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPass !== $confirmNew) {
        $error = 'New passwords do not match.';
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $_SESSION['user_id']]);
        $success = 'Password changed successfully.';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-body p-4">
                <h4 class="mb-4"><i class="bi bi-person-circle"></i> My Profile</h4>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
                        <p><strong>Role:</strong>
                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'teacher' ? 'warning' : 'info') ?>">
                                <?= ucfirst(e($user['role'])) ?>
                            </span>
                        </p>
                        <p><strong>Member since:</strong> <?= e(date('F j, Y', strtotime($user['created_at']))) ?></p>
                    </div>
                </div>

                <hr>

                <!-- Update Name -->
                <h5 class="mb-3">Update Profile</h5>
                <form method="POST" action="" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= e($user['name']) ?>" required>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Profile
                        </button>
                    </div>
                </form>

                <hr>

                <!-- Change Password -->
                <h5 class="mb-3">Change Password</h5>
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password"
                                   name="current_password" required>
                        </div>
                        <div class="col-md-4">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password"
                                   name="new_password" minlength="6" required>
                        </div>
                        <div class="col-md-4">
                            <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_new_password"
                                   name="confirm_new_password" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="bi bi-key"></i> Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

