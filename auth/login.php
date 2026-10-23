<?php
/**
 * User Login
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

$pageTitle = 'Login';

if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role'];

                setFlashMessage('success', 'Welcome back, ' . e($user['name']) . '!');

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect(BASE_URL . '/admin/dashboard.php');
                } elseif ($user['role'] === 'teacher') {
                    redirect(BASE_URL . '/teacher/dashboard.php');
                } else {
                    redirect(BASE_URL . '/index.php');
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 auth-card">
        <div class="card shadow">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">
                    <i class="bi bi-box-arrow-in-right text-primary"></i> Login
                </h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </form>

                <p class="text-center mt-3 mb-0">
                    Don't have an account?
                    <a href="<?= BASE_URL ?>/auth/register.php">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

