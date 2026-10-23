<?php
/**
 * User Registration
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

$pageTitle = 'Register';

if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? 'student';

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['student', 'teacher'])) {
        $error = 'Invalid role selected.';
    } else {
        try {
            $pdo = getConnection();

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                // Hash password and insert
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPassword, $role]);

                setFlashMessage('success', 'Registration successful! Please login.');
                redirect(BASE_URL . '/auth/login.php');
            }
        } catch (PDOException $e) {
            $error = 'Registration failed: ' . $e->getMessage();
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
                    <i class="bi bi-person-plus text-primary"></i> Create Account
                </h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= e($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                               minlength="6" required>
                        <div class="form-text">At least 6 characters.</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password"
                               name="confirm_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Register as</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher / Creator</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-check"></i> Register
                    </button>
                </form>

                <p class="text-center mt-3 mb-0">
                    Already have an account?
                    <a href="<?= BASE_URL ?>/auth/login.php">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

