<?php
/**
 * Edit Lesson
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn() || !hasRole('teacher')) {
    setFlashMessage('danger', 'Access denied.');
    redirect(BASE_URL . '/index.php');
}

$pageTitle = 'Edit Lesson';
$pdo = getConnection();
$userId = $_SESSION['user_id'];

$lessonId = intval($_GET['id'] ?? 0);
if ($lessonId <= 0) {
    setFlashMessage('danger', 'Invalid lesson ID.');
    redirect(BASE_URL . '/teacher/dashboard.php');
}

// Fetch lesson (ensure it belongs to the current teacher)
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ? AND user_id = ?");
$stmt->execute([$lessonId, $userId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    setFlashMessage('danger', 'Lesson not found or access denied.');
    redirect(BASE_URL . '/teacher/dashboard.php');
}

$categories = getCategories($pdo);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type        = $_POST['type'] ?? $lesson['type'];
    $categoryId  = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $youtubeUrl  = trim($_POST['youtube_url'] ?? '');

    if (empty($title) || empty($description)) {
        $error = 'Title and description are required.';
    } else {
        $filePath = $lesson['file_path']; // Keep existing file by default

        // Handle file upload if a new file is provided
        if (!empty($_FILES['file']['name'])) {
            $allowedExts = ($type === 'video') ? ['mp4', 'webm', 'ogg'] : ['pdf'];
            $fileExt = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $fileSize = $_FILES['file']['size'];

            if (!in_array($fileExt, $allowedExts)) {
                $error = 'Invalid file type.';
            } elseif ($fileSize > 100 * 1024 * 1024) {
                $error = 'File too large. Maximum 100MB.';
            } else {
                // Delete old file if exists
                if ($lesson['file_path'] && file_exists(__DIR__ . '/../' . $lesson['file_path'])) {
                    unlink(__DIR__ . '/../' . $lesson['file_path']);
                }

                $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
                $uploadDir = __DIR__ . '/../uploads/' . ($type === 'video' ? 'videos' : 'pdfs');
                $destination = $uploadDir . '/' . $newFileName;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
                    $filePath = 'uploads/' . ($type === 'video' ? 'videos' : 'pdfs') . '/' . $newFileName;
                } else {
                    $error = 'File upload failed.';
                }
            }
        }

        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE lessons
                    SET title = ?, description = ?, category_id = ?,
                        youtube_url = ?, file_path = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([
                    $title,
                    $description,
                    $categoryId,
                    !empty($youtubeUrl) ? $youtubeUrl : null,
                    $filePath,
                    $lessonId,
                    $userId
                ]);

                // Reset status to pending for admin re-approval
                $stmt = $pdo->prepare("UPDATE lessons SET status = 'pending' WHERE id = ?");
                $stmt->execute([$lessonId]);

                setFlashMessage('success', 'Lesson updated successfully. It needs re-approval.');
                redirect(BASE_URL . '/teacher/dashboard.php');
            } catch (PDOException $e) {
                $error = 'Update failed: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-body p-4">
                <h4 class="mb-4"><i class="bi bi-pencil"></i> Edit Lesson</h4>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Lesson Title *</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= e($_POST['title'] ?? $lesson['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description"
                                  rows="4" required><?= e($_POST['description'] ?? $lesson['description']) ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Lesson Type</label>
                            <input type="text" class="form-control" value="<?= ucfirst(e($lesson['type'])) ?>" readonly>
                            <input type="hidden" name="type" value="<?= e($lesson['type']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">-- No Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat['id']) ?>"
                                        <?= (intval($_POST['category_id'] ?? $lesson['category_id']) === $cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if ($lesson['type'] === 'video'): ?>
                        <div class="mb-3">
                            <label for="youtube_url" class="form-label">YouTube URL</label>
                            <input type="url" class="form-control" id="youtube_url" name="youtube_url"
                                   placeholder="https://www.youtube.com/watch?v=..."
                                   value="<?= e($_POST['youtube_url'] ?? $lesson['youtube_url']) ?>">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="file" class="form-label">
                            <?= $lesson['type'] === 'video' ? 'Upload New Video (MP4)' : 'Upload New PDF' ?>
                        </label>
                        <input type="file" class="form-control" id="file" name="file"
                               accept="<?= $lesson['type'] === 'video' ? '.mp4,.webm,.ogg' : '.pdf' ?>">
                        <?php if ($lesson['file_path']): ?>
                            <div class="form-text">
                                Current file: <?= e(basename($lesson['file_path'])) ?>.
                                Upload a new file to replace it.
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                    <a href="<?= BASE_URL ?>/teacher/dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
