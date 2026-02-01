<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_admin();

$enrollment_by_month_sql = "SELECT DATE_FORMAT(enrolled_at, '%Y-%m') as month, COUNT(*) as count
                            FROM enrollments
                            GROUP BY month
                            ORDER BY month DESC
                            LIMIT 12";
$enrollment_by_month = db_fetch_all(db_query($conn, $enrollment_by_month_sql));

$courses_by_category_sql = "SELECT category, COUNT(*) as count
                            FROM courses
                            WHERE is_published = 1
                            GROUP BY category
                            ORDER BY count DESC";
$courses_by_category = db_fetch_all(db_query($conn, $courses_by_category_sql));

$users_by_role_sql = "SELECT role, COUNT(*) as count
                      FROM users
                      GROUP BY role";
$users_by_role = db_fetch_all(db_query($conn, $users_by_role_sql));

$quiz_performance_sql = "SELECT q.title, c.title as course_title,
                         COUNT(qa.id) as total_attempts,
                         COALESCE(AVG(qa.score), 0) as avg_score,
                         COUNT(CASE WHEN qa.passed = 1 THEN 1 END) as passed_count
                         FROM quizzes q
                         JOIN courses c ON q.course_id = c.id
                         LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id
                         GROUP BY q.id
                         ORDER BY total_attempts DESC
                         LIMIT 10";
$quiz_performance = db_fetch_all(db_query($conn, $quiz_performance_sql));

$completion_rates_sql = "SELECT c.title, c.category,
                         COUNT(DISTINCT e.user_id) as enrolled,
                         COUNT(DISTINCT CASE WHEN e.is_completed = 1 THEN e.user_id END) as completed,
                         COALESCE(ROUND((COUNT(DISTINCT CASE WHEN e.is_completed = 1 THEN e.user_id END) / NULLIF(COUNT(DISTINCT e.user_id), 0)) * 100, 2), 0) as completion_rate
                         FROM courses c
                         LEFT JOIN enrollments e ON c.id = e.course_id
                         WHERE c.is_published = 1
                         GROUP BY c.id
                         HAVING enrolled > 0
                         ORDER BY completion_rate DESC
                         LIMIT 10";
$completion_rates = db_fetch_all(db_query($conn, $completion_rates_sql));

$page_title = 'Reports';
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
                <h2>🎓 Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">📊 Dashboard</a>
                <a href="users.php" class="nav-item">👥 Users</a>
                <a href="courses.php" class="nav-item">📚 Courses</a>
                <a href="categories.php" class="nav-item">📑 Categories</a>
                <a href="analytics.php" class="nav-item">📈 Analytics</a>
                <a href="reports.php" class="nav-item active">📄 Reports</a>
                <a href="settings.php" class="nav-item">⚙️ Settings</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo $page_title; ?></h1>
            </div>
            
            <?php display_message(); ?>
            
            <div class="row mb-5">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>📅 Enrollments by Month</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($enrollment_by_month)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Enrollments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrollment_by_month as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['month']); ?></td>
                                        <td><?php echo number_format($row['count'] ?? 0); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p class="text-center text-muted">No enrollment data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>📚 Courses by Category</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($courses_by_category)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses_by_category as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <td><?php echo number_format($row['count'] ?? 0); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p class="text-center text-muted">No courses available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-5">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>👥 Users by Role</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($users_by_role)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th>Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users_by_role as $row): ?>
                                    <tr>
                                        <td><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
                                        <td><?php echo number_format($row['count'] ?? 0); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p class="text-center text-muted">No user data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>✅ Course Completion Rates</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($completion_rates)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Enrolled</th>
                                        <th>Completed</th>
                                        <th>Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($completion_rates as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(truncate($row['title'], 30)); ?></td>
                                        <td><?php echo number_format($row['enrolled'] ?? 0); ?></td>
                                        <td><?php echo number_format($row['completed'] ?? 0); ?></td>
                                        <td><?php echo number_format($row['completion_rate'] ?? 0, 1); ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p class="text-center text-muted">No completion data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>🎯 Quiz Performance</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($quiz_performance)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Quiz</th>
                                <th>Course</th>
                                <th>Total Attempts</th>
                                <th>Average Score</th>
                                <th>Pass Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quiz_performance as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(truncate($row['title'], 30)); ?></td>
                                <td><?php echo htmlspecialchars(truncate($row['course_title'], 30)); ?></td>
                                <td><?php echo number_format($row['total_attempts'] ?? 0); ?></td>
                                <td><?php echo number_format($row['avg_score'] ?? 0, 2); ?>%</td>
                                <td>
                                    <?php 
                                    $total = $row['total_attempts'] ?? 0;
                                    $passed = $row['passed_count'] ?? 0;
                                    $pass_rate = $total > 0 ? ($passed / $total) * 100 : 0;
                                    ?>
                                    <?php echo number_format($pass_rate, 1); ?>%
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-center text-muted">No quiz data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>