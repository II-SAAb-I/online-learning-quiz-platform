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

$course_sql = "SELECT title FROM courses WHERE id = ?";
$course_result = db_query($conn, $course_sql, "i", [$course_id]);
$course = db_fetch_assoc($course_result);

$lessons_sql = "SELECT * FROM lessons WHERE course_id = ? ORDER BY lesson_order";
$lessons = db_fetch_all(db_query($conn, $lessons_sql, "i", [$course_id]));

$page_title = 'Manage Lessons';
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
                <a href="courses.php" class="nav-item">📚 My Courses</a>
                <a href="course-create.php" class="nav-item">➕ Create Course</a>
                <a href="students.php" class="nav-item">👥 Students</a>
                <a href="analytics.php" class="nav-item">📈 Analytics</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo $page_title; ?>: <?php echo htmlspecialchars($course['title']); ?></h1>
                <div>
                    <a href="lesson-create.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">+ Add Lesson</a>
                    <a href="course-edit.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">← Back to Course</a>
                </div>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($lessons)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Title</th>
                                <th>Duration (min)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lessons as $lesson): ?>
                            <tr>
                                <td><?php echo $lesson['lesson_order']; ?></td>
                                <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                                <td><?php echo $lesson['estimated_duration']; ?></td>
                                <td>
                                    <a href="lesson-edit.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="lesson-delete.php?id=<?php echo $lesson['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this lesson?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-center text-muted">No lessons yet. Add your first lesson!</p>
                    <div class="text-center">
                        <a href="lesson-create.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">Add First Lesson</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>