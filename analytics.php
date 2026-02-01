<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();
$user_id = get_user_id();

$sql = "SELECT c.title, COUNT(DISTINCT e.user_id) as enrollments, COALESCE(AVG(e.progress_percentage), 0) as avg_progress
        FROM courses c
        LEFT JOIN enrollments e ON c.id = e.course_id
        WHERE c.created_by = ?
        GROUP BY c.id";
$analytics = db_fetch_all(db_query($conn, $sql, "i", [$user_id]));
?>
<!DOCTYPE html>
<html>

<head>
    <title>Analytics</title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/dashboard.css">
</head>

<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>👨‍🏫 Instructor</h2>
            </div>
            <nav class="sidebar-nav"><a href="dashboard.php" class="nav-item">📊 Dashboard</a><a href="courses.php"
                    class="nav-item">📚 My Courses</a><a href="analytics.php" class="nav-item active">📈 Analytics</a><a
                    href="../logout.php" class="nav-item">🚪 Logout</a></nav>
        </aside>
        <main class="main-content">
            <h1>Course Analytics</h1>
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Enrollments</th>
                                <th>Avg Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analytics as $a): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['title']); ?></td>
                                    <td><?php echo $a['enrollments']; ?></td>
                                    <td><?php echo number_format($a['avg_progress'], 1); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>