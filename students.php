<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();

$sql = "SELECT u.id, u.full_name, u.email, c.title as course_title, c.id as course_id,
        e.progress_percentage, e.is_completed, e.enrolled_at
        FROM enrollments e
        JOIN users u ON e.user_id = u.id
        JOIN courses c ON e.course_id = c.id
        WHERE c.created_by = ?
        ORDER BY e.enrolled_at DESC";

$students = db_fetch_all(db_query($conn, $sql, "i", [$user_id]));

$page_title = 'My Students';
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
                <a href="students.php" class="nav-item active">👥 Students</a>
                <a href="analytics.php" class="nav-item">📈 Analytics</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo $page_title; ?></h1>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($students)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Enrolled</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <a href="course-edit.php?id=<?php echo $student['course_id']; ?>">
                                        <?php echo htmlspecialchars($student['course_title']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar" style="width: <?php echo $student['progress_percentage']; ?>%;">
                                            <?php echo number_format($student['progress_percentage'], 1); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($student['is_completed']): ?>
                                        <span class="badge badge-success">Completed</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">In Progress</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo time_ago($student['enrolled_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-center text-muted">No students enrolled in your courses yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>