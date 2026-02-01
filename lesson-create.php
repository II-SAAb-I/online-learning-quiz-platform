<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();
$course_id = intval($_GET['course_id'] ?? 0);

if (!$course_id) {
    set_message('error', 'Invalid course');
    redirect('courses.php');
}

if (!owns_resource($conn, 'courses', $course_id, 'created_by', $user_id)) {
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

        // If no duration provided but video URL exists, try to get video duration
        if ($estimated_duration == 0 && !empty($video_url)) {
            $video_duration = get_video_duration($video_url);
            if ($video_duration) {
                $estimated_duration = $video_duration;
            }
        }

        if (empty($title)) {
            set_message('error', 'Lesson title is required');
        } else {
            $sql = "INSERT INTO lessons (course_id, title, content, video_url, lesson_order, estimated_duration)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $result = db_query($conn, $sql, "issiii", [$course_id, $title, $content, $video_url, $lesson_order, $estimated_duration]);
            
            if ($result) {
                set_message('success', 'Lesson created successfully');
                redirect('lessons.php?course_id=' . $course_id);
            } else {
                set_message('error', 'Failed to create lesson');
            }
        }
    }
}

$page_title = 'Create Lesson';
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
                <a href="lessons.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">← Back to Lessons</a>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label for="title">Lesson Title *</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Lesson Content</label>
                            <textarea id="content" name="content" class="form-control" rows="10" placeholder="Enter lesson content, instructions, or notes..."></textarea>
                            <small class="form-text">You can include text content, instructions, or explanations here</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="video_url">Video URL (Optional)</label>
                            <input type="url" id="video_url" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                            <small class="form-text">YouTube, Vimeo, or any embeddable video URL</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="lesson_order">Lesson Order</label>
                                    <input type="number" id="lesson_order" name="lesson_order" class="form-control" value="1" min="1">
                                    <small class="form-text">The order in which this lesson appears in the course</small>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="estimated_duration">Estimated Duration (minutes)</label>
                                    <input type="number" id="estimated_duration" name="estimated_duration" class="form-control" value="0" min="0">
                                    <small class="form-text">How long it takes to complete this lesson</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Create Lesson</button>
                            <a href="lessons.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>