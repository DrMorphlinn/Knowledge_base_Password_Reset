<?php
/**
 * Create Lesson
 * Knowledge Base Portal
 */
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn() || !hasRole('teacher')) {
    setFlashMessage('danger', 'Access denied.');
    redirect(BASE_URL . '/index.php');
}

$pageTitle = 'Create Lesson';
$pdo = getConnection();
$categories = getCategories($pdo);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type        = $_POST['type'] ?? '';
    $categoryId  = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $youtubeUrl  = trim($_POST['youtube_url'] ?? '');

    // Validation
    if (empty($title) || empty($description) || empty($type)) {
        $error = 'Title, description, and type are required.';
    } elseif (!in_array($type, ['video', 'pdf'])) {
        $error = 'Invalid lesson type.';
    } else {
        $filePath = null;

        if ($type === 'video' && !empty($youtubeUrl)) {
            // Use YouTube URL
            $filePath = null;
        } elseif (!empty($_FILES['file']['name'])) {
            // Handle file upload
            $allowedVideoExts = ['mp4', 'webm', 'ogg'];
            $allowedPdfExts   = ['pdf'];
            $allowedExts      = ($type === 'video') ? $allowedVideoExts : $allowedPdfExts;
            $fileName         = $_FILES['file']['name'];
            $fileExt          = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileSize         = $_FILES['file']['size'];
            $fileTmp          = $_FILES['file']['tmp_name'];

            if (!in_array($fileExt, $allowedExts)) {
                $error = 'Invalid file type. Allowed: ' . implode(', ', $allowedExts);
            } elseif ($fileSize > 100 * 1024 * 1024) { // 100MB max
                $error = 'File too large. Maximum 100MB.';
            } else {
                // Generate unique filename
                $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
                $uploadDir = __DIR__ . '/../uploads/' . ($type === 'video' ? 'videos' : 'pdfs');
                $destination = $uploadDir . '/' . $newFileName;

                if (move_uploaded_file($fileTmp, $destination)) {
                    $filePath = 'uploads/' . ($type === 'video' ? 'videos' : 'pdfs') . '/' . $newFileName;
                } else {
                    $error = 'File upload failed. Check directory permissions.';
                }
            }
        } elseif ($type === 'video' && empty($youtubeUrl)) {
            $error = 'Please provide a YouTube URL or upload a video file.';
        } elseif ($type === 'pdf') {
            $error = 'Please upload a PDF file.';
        }

        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO lessons (title, description, type, file_path, youtube_url, category_id, user_id, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([
                    $title,
                    $description,
                    $type,
                    $filePath,
                    !empty($youtubeUrl) ? $youtubeUrl : null,
                    $categoryId,
                    $_SESSION['user_id']
                ]);

                setFlashMessage('success', 'Lesson created successfully! It is pending admin approval.');
                redirect(BASE_URL . '/teacher/dashboard.php');
            } catch (PDOException $e) {
                $error = 'Failed to create lesson: ' . $e->getMessage();
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
                <h4 class="mb-4"><i class="bi bi-plus-circle"></i> Create New Lesson</h4>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Lesson Title *</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= e($_POST['title'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description"
                                  rows="4" required><?= e($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label">Lesson Type *</label>
                            <select class="form-select" id="type" name="type" required
                                    onchange="toggleLessonType()">
                                <option value="">-- Select Type --</option>
                                <option value="video" <?= ($_POST['type'] ?? '') === 'video' ? 'selected' : '' ?>>Video</option>
                                <option value="pdf" <?= ($_POST['type'] ?? '') === 'pdf' ? 'selected' : '' ?>>PDF</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">-- No Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat['id']) ?>"
                                        <?= (isset($_POST['category_id']) && intval($_POST['category_id']) === $cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Video options -->
                    <div id="video-options" class="mb-3" style="display: none;">
                        <div class="card bg-light p-3">
                            <h6>Video Source Options</h6>
                            <div class="mb-3">
                                <label for="youtube_url" class="form-label">YouTube URL (optional if uploading video)</label>
                                <input type="url" class="form-control" id="youtube_url" name="youtube_url"
                                       placeholder="https://www.youtube.com/watch?v=..."
                                       value="<?= e($_POST['youtube_url'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="video_file" class="form-label">Or Upload MP4 Video</label>
                                <input type="file" class="form-control" id="video_file" name="file"
                                       accept=".mp4,.webm,.ogg">
                                <div class="form-text">Max 100MB. Leave empty if using YouTube URL.</div>
                            </div>
                        </div>
                    </div>

                    <!-- PDF options -->
                    <div id="pdf-options" class="mb-3" style="display: none;">
                        <div class="card bg-light p-3">
                            <h6>Upload PDF</h6>
                            <div class="mb-3">
                                <label for="pdf_file" class="form-label">PDF File</label>
                                <input type="file" class="form-control" id="pdf_file" name="file" accept=".pdf">
                                <div class="form-text">Max 100MB.</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Create Lesson
                    </button>
                    <a href="<?= BASE_URL ?>/teacher/dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLessonType() {
    const type = document.getElementById('type').value;
    document.getElementById('video-options').style.display = (type === 'video') ? 'block' : 'none';
    document.getElementById('pdf-options').style.display = (type === 'pdf') ? 'block' : 'none';

    // Remove required attributes to avoid blocking submission
    document.getElementById('video_file').required = false;
    document.getElementById('pdf_file').required = false;
}

// Run on page load if type was previously selected
toggleLessonType();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
