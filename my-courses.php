<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$user_id = get_user_id();

$sql = "SELECT c.*, e.progress_percentage, e.is_completed, e.enrolled_at,
        u.full_name as instructor_name,
        COUNT(DISTINCT l.id) as total_lessons,
        COUNT(DISTINCT lc.id) as completed_lessons
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        JOIN users u ON c.created_by = u.id
        LEFT JOIN lessons l ON c.id = l.course_id
        LEFT JOIN lesson_completion lc ON l.id = lc.lesson_id AND lc.user_id = e.user_id
        WHERE e.user_id = ?
        GROUP BY c.id
        ORDER BY e.enrolled_at DESC";

$courses = db_fetch_all(db_query($conn, $sql, "i", [$user_id]));

$page_title = 'My Courses';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background: linear-gradient(135deg, #fef5f1 0%, #fef8f5 100%);
            color: #2d3748;
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

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

        .sidebar-nav {
            padding: 24px 16px;
        }

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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .content-header h1 {
            font-size: clamp(28px, 3vw, 36px);
            font-weight: 800;
            color: #4a5568;
            letter-spacing: -0.5px;
        }

        .btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 165, 165, 0.4);
        }

        .btn-block {
            width: 100%;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 28px;
        }

        .course-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            border: 1px solid #f7dfd4;
            display: flex;
            flex-direction: column;
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
            border-color: #d4a5a5;
        }

        .course-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #d4a5a5, #f7dfd4);
        }

        .course-body {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }

        .badge-success {
            background: linear-gradient(135deg, rgba(134, 195, 168, 0.2) 0%, rgba(134, 195, 168, 0.3) 100%);
            color: #4a7c59;
        }

        .badge-primary {
            background: linear-gradient(135deg, rgba(212, 165, 165, 0.2) 0%, rgba(199, 154, 148, 0.3) 100%);
            color: #9c6b65;
        }

        .course-title {
            font-size: 20px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .course-description {
            font-size: 14px;
            color: #718096;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .progress-section {
            margin-bottom: 20px;
        }

        .progress-label {
            font-size: 13px;
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

        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #f7dfd4;
            margin-bottom: 20px;
            font-size: 13px;
            color: #a0aec0;
        }

        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 80px 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #f7dfd4;
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 24px;
            opacity: 0.4;
        }

        .empty-state h3 {
            font-size: 24px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 12px;
        }

        .empty-state p {
            font-size: 16px;
            color: #718096;
            margin-bottom: 32px;
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
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
                padding: 24px;
            }

            .course-grid {
                grid-template-columns: 1fr;
            }
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
                <a href="dashboard.php" class="nav-item">📊 Dashboard</a>
                <a href="courses.php" class="nav-item">📚 Browse Courses</a>
                <a href="my-courses.php" class="nav-item active">📖 My Courses</a>
                <a href="my-certificates.php" class="nav-item">🏆 Certificates</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1>📖 My Courses</h1>
                <a href="courses.php" class="btn btn-primary">+ Browse More</a>
            </div>
            
            <?php display_message(); ?>
            
            <?php if (!empty($courses)): ?>
                <div class="course-grid">
                    <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <?php if ($course['thumbnail']): ?>
                            <img src="<?php echo UPLOADS_URL . '/courses/' . htmlspecialchars($course['thumbnail']); ?>" class="course-image" alt="Course">
                        <?php else: ?>
                            <div class="course-image"></div>
                        <?php endif; ?>
                        
                        <div class="course-body">
                            <?php if ($course['is_completed']): ?>
                                <span class="badge badge-success">✅ Completed</span>
                            <?php else: ?>
                                <span class="badge badge-primary">📖 In Progress</span>
                            <?php endif; ?>
                            
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="course-description"><?php echo htmlspecialchars(truncate($course['short_description'], 100)); ?></p>
                            
                            <div class="progress-section">
                                <div class="progress-label">
                                    <span>Progress</span>
                                    <span class="progress-percentage"><?php echo number_format($course['progress_percentage'], 0); ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $course['is_completed'] ? 'complete' : ''; ?>" style="width: <?php echo $course['progress_percentage']; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="course-meta">
                                <span>👨‍🏫 <?php echo htmlspecialchars($course['instructor_name']); ?></span>
                                <span>📚 <?php echo $course['completed_lessons']; ?>/<?php echo $course['total_lessons']; ?> lessons</span>
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
        </main>
    </div>
</body>
</html>