<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();
$course_id = intval($_GET['id'] ?? 0);

if (!$course_id) {
    set_message('error', 'Invalid course');
    redirect('courses.php');
}

if (!owns_resource($conn, 'courses', $course_id, 'created_by', $user_id)) {
    set_message('error', 'Access denied');
    redirect('courses.php');
}

$sql = "SELECT * FROM courses WHERE id = ?";
$result = db_query($conn, $sql, "i", [$course_id]);
$course = db_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token');
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $short_description = sanitize($_POST['short_description'] ?? '');
        $long_description = sanitize($_POST['long_description'] ?? '');
        $category = sanitize($_POST['category'] ?? '');
        $difficulty = sanitize($_POST['difficulty'] ?? 'beginner');
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        
        if (empty($title) || empty($short_description) || empty($category)) {
            set_message('error', 'Please fill all required fields');
        } else {
            $thumbnail = $course['thumbnail'];
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                $upload_result = upload_file($_FILES['thumbnail'], UPLOAD_PATH . '/courses', ALLOWED_IMAGE_TYPES);
                if ($upload_result['success']) {
                    if ($thumbnail) {
                        delete_file(UPLOAD_PATH . '/courses/' . $thumbnail);
                    }
                    $thumbnail = $upload_result['filename'];
                }
            }
            
            $sql = "UPDATE courses SET title = ?, short_description = ?, long_description = ?, category = ?, difficulty = ?, thumbnail = ?, is_published = ? WHERE id = ?";
            $result = db_query($conn, $sql, "sssssiii", [$title, $short_description, $long_description, $category, $difficulty, $thumbnail, $is_published, $course_id]);
            
            if ($result) {
                set_message('success', 'Course updated successfully');
                redirect('course-edit.php?id=' . $course_id);
            } else {
                set_message('error', 'Failed to update course');
            }
        }
    }
}

$page_title = 'Edit Course';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/dashboard.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>👨‍🏫 Instructor</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">📊 Dashboard</a>
                <a href="courses.php" class="nav-item active">📚 My Courses</a>
                <a href="course-create.php" class="nav-item">➕ Create Course</a>
                <a href="students.php" class="nav-item">👥 Students</a>
                <a href="analytics.php" class="nav-item">📈 Analytics</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo $page_title; ?></h1>
                <div>
                    <a href="lessons.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">Manage Lessons</a>
                    <a href="quizzes.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">Manage Quizzes</a>
                    <a href="courses.php" class="btn btn-secondary">← Back</a>
                </div>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label for="title">Course Title *</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($course['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="short_description">Short Description *</label>
                            <input type="text" id="short_description" name="short_description" class="form-control" value="<?php echo htmlspecialchars($course['short_description']); ?>" maxlength="500" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="long_description">Full Description</label>
                            <textarea id="long_description" name="long_description" class="form-control" rows="6"><?php echo htmlspecialchars($course['long_description']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="category">Category *</label>
                                    <input type="text" id="category" name="category" class="form-control" value="<?php echo htmlspecialchars($course['category']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="difficulty">Difficulty Level *</label>
                                    <select id="difficulty" name="difficulty" class="form-control" required>
                                        <option value="beginner" <?php echo $course['difficulty'] === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                        <option value="intermediate" <?php echo $course['difficulty'] === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                        <option value="advanced" <?php echo $course['difficulty'] === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="thumbnail">Course Thumbnail</label>
                            <?php if ($course['thumbnail']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo UPLOADS_URL . '/courses/' . htmlspecialchars($course['thumbnail']); ?>" style="max-width: 300px; border-radius: var(--border-radius);" alt="Current thumbnail">
                                </div>
                            <?php endif; ?>
                            <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="is_published" name="is_published" <?php echo $course['is_published'] ? 'checked' : ''; ?>>
                            <label for="is_published">Published</label>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Course</button>
                            <a href="courses.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>