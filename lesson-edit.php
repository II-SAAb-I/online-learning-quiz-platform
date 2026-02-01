<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();
$lesson_id = intval($_GET['id'] ?? 0);

if (!$lesson_id) {
    set_message('error', 'Invalid lesson');
    redirect('courses.php');
}

$sql = "SELECT l.*, c.created_by, c.id as course_id FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.id = ?";
$result = db_query($conn, $sql, "i", [$lesson_id]);
$lesson = db_fetch_assoc($result);

if (!$lesson || !owns_resource($conn, 'courses', $lesson['course_id'], 'created_by', $user_id)) {
    set_message('error', 'Access denied');
    redirect('courses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token');
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $content = sanitize($_POST['content'] ?? '');
        $video_url = sanitize($_POST['video_url'] ?? '');
        $lesson_order = intval($_POST['lesson_order'] ?? 1);
        $estimated_duration = intval($_POST['estimated_duration'] ?? 0);
        
        if (empty($title)) {
            set_message('error', 'Lesson title is required');
        } else {
            $sql = "UPDATE lessons SET title = ?, content = ?, video_url = ?, lesson_order = ?, estimated_duration = ? WHERE id = ?";
            $result = db_query($conn, $sql, "sssiii", [$title, $content, $video_url, $lesson_order, $estimated_duration, $lesson_id]);
            
            if ($result) {
                set_message('success', 'Lesson updated successfully');
                redirect('lessons.php?course_id=' . $lesson['course_id']);
            } else {
                set_message('error', 'Failed to update lesson');
            }
        }
    }
}

$page_title = 'Edit Lesson';
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
                <a href="lessons.php?course_id=<?php echo $lesson['course_id']; ?>" class="btn btn-secondary">← Back to Lessons</a>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label for="title">Lesson Title *</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Lesson Content</label>
                            <textarea id="content" name="content" class="form-control" rows="10"><?php echo htmlspecialchars($lesson['content']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="video_url">Video URL (Optional)</label>
                            <input type="url" id="video_url" name="video_url" class="form-control" value="<?php echo htmlspecialchars($lesson['video_url']); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="lesson_order">Lesson Order</label>
                                    <input type="number" id="lesson_order" name="lesson_order" class="form-control" value="<?php echo $lesson['lesson_order']; ?>" min="1">
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="estimated_duration">Estimated Duration (minutes)</label>
                                    <input type="number" id="estimated_duration" name="estimated_duration" class="form-control" value="<?php echo $lesson['estimated_duration']; ?>" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Lesson</button>
                            <a href="lessons.php?course_id=<?php echo $lesson['course_id']; ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>