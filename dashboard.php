<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$user_id = get_user_id();

$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM enrollments WHERE user_id = ?) as enrolled_courses,
    (SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND is_completed = 1) as completed_courses,
    (SELECT COUNT(DISTINCT lc.lesson_id) FROM lesson_completion lc 
     JOIN lessons l ON lc.lesson_id = l.id 
     JOIN enrollments e ON l.course_id = e.course_id 
     WHERE lc.user_id = ? AND e.user_id = ?) as completed_lessons,
    (SELECT COUNT(*) FROM certificates WHERE user_id = ?) as total_certificates";

$stats_result = db_query($conn, $stats_sql, "iiiii", [$user_id, $user_id, $user_id, $user_id, $user_id]);
$stats = db_fetch_assoc($stats_result);

$my_courses_sql = "SELECT c.*, e.progress_percentage, e.is_completed, e.enrolled_at,
                   COUNT(DISTINCT l.id) as total_lessons,
                   COUNT(DISTINCT lc.id) as completed_lessons
                   FROM enrollments e
                   JOIN courses c ON e.course_id = c.id
                   LEFT JOIN lessons l ON c.id = l.course_id
                   LEFT JOIN lesson_completion lc ON l.id = lc.lesson_id AND lc.user_id = e.user_id
                   WHERE e.user_id = ?
                   GROUP BY c.id
                   ORDER BY e.enrolled_at DESC
                   LIMIT 6";

$my_courses = db_fetch_all(db_query($conn, $my_courses_sql, "i", [$user_id]));

$recent_activity_sql = "SELECT 'lesson' as type, l.title, c.title as course_title, lc.completed_at as date
                        FROM lesson_completion lc
                        JOIN lessons l ON lc.lesson_id = l.id
                        JOIN courses c ON l.course_id = c.id
                        WHERE lc.user_id = ?
                        UNION ALL
                        SELECT 'quiz' as type, q.title, c.title as course_title, qa.completed_at as date
                        FROM quiz_attempts qa
                        JOIN quizzes q ON qa.quiz_id = q.id
                        JOIN courses c ON q.course_id = c.id
                        WHERE qa.user_id = ?
                        ORDER BY date DESC
                        LIMIT 10";

$recent_activity = db_fetch_all(db_query($conn, $recent_activity_sql, "ii", [$user_id, $user_id]));

$page_title = 'Student Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background: linear-gradient(135deg, #fef5f1 0%, #fef8f5 100%);
            color: #2d3748;
        }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #c9ada7 0%, #a89f9c 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
            z-index: 1000;
        }
        .sidebar-header {
            padding: 32px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .sidebar-header h2 {
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
        }
        .sidebar-nav { padding: 24px 16px; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 500;
        }
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(4px);
        }
        .nav-item.active {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(212, 165, 165, 0.4);
        }
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
        }
        .content-header {
            margin-bottom: 40px;
        }
        .content-header h1 {
            font-size: clamp(28px, 3vw, 36px);
            font-weight: 800;
            color: #4a5568;
            letter-spacing: -0.5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 2px solid #f7dfd4;
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
            border-color: #d4a5a5;
        }
        .stat-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            flex-shrink: 0;
        }
        .stat-details h3 {
            font-size: 32px;
            font-weight: 800;
            color: #4a5568;
            margin-bottom: 4px;
        }
        .stat-details p {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
            margin: 0;
        }
        .section {
            margin-bottom: 40px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .section-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #4a5568;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 165, 165, 0.4);
        }
        .btn-block { width: 100%; }
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }
        .course-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid #f7dfd4;
            transition: all 0.3s ease;
        }
        .course-card:hover {
            border-color: #d4a5a5;
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(212, 165, 165, 0.15);
        }
        .course-card-body {
            padding: 24px;
        }
        .course-card h4 {
            font-size: 18px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .course-description {
            font-size: 14px;
            color: #718096;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .progress-section {
            margin-bottom: 20px;
        }
        .progress-label {
            font-size: 12px;
            color: #718096;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .progress-percentage {
            font-weight: 700;
            color: #d4a5a5;
        }
        .progress {
            height: 8px;
            background: #f7dfd4;
            border-radius: 8px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #d4a5a5 0%, #c79a94 100%);
            transition: width 0.6s ease;
            border-radius: 8px;
        }
        .progress-bar.complete {
            background: linear-gradient(90deg, #86c3a8 0%, #6fba94 100%);
        }
        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
        }
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.4;
        }
        .empty-state h3 {
            font-size: 20px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 8px;
        }
        .empty-state p {
            font-size: 15px;
            color: #718096;
            margin-bottom: 24px;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
        }
        .activity-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .activity-table thead th {
            font-size: 12px;
            font-weight: 700;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 2px solid #f7dfd4;
        }
        .activity-table tbody tr {
            background: #fef8f5;
            transition: all 0.2s ease;
        }
        .activity-table tbody tr:hover {
            background: white;
            transform: translateX(4px);
        }
        .activity-table tbody td {
            padding: 16px;
            font-size: 14px;
            border-top: 1px solid #f7dfd4;
            border-bottom: 1px solid #f7dfd4;
        }
        .activity-table tbody td:first-child {
            border-left: 1px solid #f7dfd4;
            border-radius: 12px 0 0 12px;
        }
        .activity-table tbody td:last-child {
            border-right: 1px solid #f7dfd4;
            border-radius: 0 12px 12px 0;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-primary {
            background: linear-gradient(135deg, rgba(212, 165, 165, 0.15) 0%, rgba(199, 154, 148, 0.15) 100%);
            color: #9c6b65;
        }
        .badge-success {
            background: rgba(134, 195, 168, 0.15);
            color: #4a7c59;
        }
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .alert-success {
            background: rgba(134, 195, 168, 0.15);
            border: 1px solid #86c3a8;
            color: #4a7c59;
        }
        .alert-error {
            background: rgba(212, 165, 165, 0.15);
            border: 1px solid #d4a5a5;
            color: #9c6b65;
        }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; padding: 24px; }
            .stats-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
            .course-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .stat-card { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🎓 Student Portal</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">📊 Dashboard</a>
                <a href="courses.php" class="nav-item">📚 Browse Courses</a>
                <a href="my-courses.php" class="nav-item">📖 My Courses</a>
                <a href="my-certificates.php" class="nav-item">🏆 Certificates</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h1>
            </div>
            
            <?php display_message(); ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['enrolled_courses']; ?></h3>
                        <p>Enrolled Courses</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['completed_courses']; ?></h3>
                        <p>Completed Courses</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📖</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['completed_lessons']; ?></h3>
                        <p>Lessons Completed</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_certificates']; ?></h3>
                        <p>Certificates Earned</p>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-header">
                    <h2>Continue Learning</h2>
                    <a href="courses.php" class="btn btn-primary">Browse More</a>
                </div>
                
                <?php if (!empty($my_courses)): ?>
                    <div class="course-grid">
                        <?php foreach ($my_courses as $course): ?>
                        <div class="course-card">
                            <div class="course-card-body">
                                <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                                <p class="course-description"><?php echo htmlspecialchars(truncate($course['short_description'], 80)); ?></p>
                                
                                <div class="progress-section">
                                    <div class="progress-label">
                                        <span>Progress</span>
                                        <span class="progress-percentage"><?php echo number_format($course['progress_percentage'], 0); ?>%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar <?php echo $course['is_completed'] ? 'complete' : ''; ?>" style="width: <?php echo $course['progress_percentage']; ?>%"></div>
                                    </div>
                                </div>
                                
                                <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-block">
                                    <?php echo $course['is_completed'] ? 'Review Course' : 'Continue Learning'; ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <h3>You haven't enrolled in any courses yet</h3>
                        <p>Start learning by browsing our course catalog</p>
                        <a href="courses.php" class="btn btn-primary">Browse Courses</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="section">
                <div class="section-header">
                    <h2>Recent Activity</h2>
                </div>
                
                <div class="card">
                    <?php if (!empty($recent_activity)): ?>
                        <table class="activity-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Activity</th>
                                    <th>Course</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_activity as $activity): ?>
                                <tr>
                                    <td>
                                        <?php if ($activity['type'] === 'lesson'): ?>
                                            <span class="badge badge-primary">Lesson</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Quiz</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight: 600; color: #4a5568;"><?php echo htmlspecialchars($activity['title']); ?></td>
                                    <td style="color: #718096;"><?php echo htmlspecialchars(truncate($activity['course_title'], 40)); ?></td>
                                    <td style="color: #a0aec0; font-size: 13px;"><?php echo time_ago($activity['date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📊</div>
                            <p>No recent activity to show</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>