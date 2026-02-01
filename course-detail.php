<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$course_id = intval($_GET['id'] ?? 0);
$user_id = get_user_id();

if (!$course_id) {
    set_message('error', 'Invalid course');
    redirect('courses.php');
}

$sql = "SELECT c.*, u.full_name as instructor_name,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND user_id = ?) as is_enrolled
        FROM courses c
        JOIN users u ON c.created_by = u.id
        WHERE c.id = ? AND c.is_published = 1";

$result = db_query($conn, $sql, "ii", [$user_id, $course_id]);

if (!$result || db_num_rows($result) == 0) {
    set_message('error', 'Course not found');
    redirect('courses.php');
}

$course = db_fetch_assoc($result);

$lessons_sql = "SELECT * FROM lessons WHERE course_id = ? ORDER BY lesson_order";
$lessons = db_fetch_all(db_query($conn, $lessons_sql, "i", [$course_id]));

$quizzes_sql = "SELECT * FROM quizzes WHERE course_id = ? ORDER BY id";
$quizzes = db_fetch_all(db_query($conn, $quizzes_sql, "i", [$course_id]));

if ($course['is_enrolled']) {
    $progress_sql = "SELECT progress_percentage, is_completed FROM enrollments WHERE course_id = ? AND user_id = ?";
    $progress_result = db_query($conn, $progress_sql, "ii", [$course_id, $user_id]);
    $progress = db_fetch_assoc($progress_result);
}

$page_title = $course['title'];
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
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 800;
            color: #4a5568;
            letter-spacing: -0.5px;
            flex: 1;
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

        .btn-sm {
            padding: 10px 20px;
            font-size: 14px;
        }

        .progress-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
        }

        .progress-card h4 {
            font-size: 18px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 16px;
        }

        .progress {
            height: 32px;
            background: #f7dfd4;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #d4a5a5 0%, #c79a94 100%);
            transition: width 0.6s ease;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        .progress-bar.complete {
            background: linear-gradient(90deg, #86c3a8 0%, #6fba94 100%);
        }

        .course-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
            margin-bottom: 32px;
        }

        .card h3 {
            font-size: 22px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 20px;
        }

        .badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-right: 8px;
            margin-bottom: 16px;
        }

        .badge-primary {
            background: linear-gradient(135deg, rgba(212, 165, 165, 0.2) 0%, rgba(199, 154, 148, 0.3) 100%);
            color: #9c6b65;
        }

        .badge-secondary {
            background: rgba(113, 128, 150, 0.15);
            color: #4a5568;
        }

        .course-description {
            line-height: 1.8;
            color: #718096;
            margin-bottom: 24px;
        }

        .course-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding-top: 24px;
            border-top: 2px solid #f7dfd4;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item strong {
            display: block;
            font-size: 13px;
            color: #a0aec0;
            margin-bottom: 4px;
        }

        .stat-item span {
            display: block;
            font-size: 18px;
            font-weight: 700;
            color: #4a5568;
        }

        .lesson-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .lesson-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #fef8f5;
            border: 2px solid #f7dfd4;
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .lesson-item:hover {
            background: white;
            border-color: #d4a5a5;
            transform: translateX(4px);
        }

        .lesson-info h5 {
            font-size: 16px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 4px;
        }

        .lesson-info small {
            color: #a0aec0;
            font-size: 13px;
        }

        .instructor-card {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
            text-align: center;
            padding: 32px;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(212, 165, 165, 0.3);
        }

        .instructor-card h4 {
            color: white;
            margin-bottom: 8px;
        }

        .instructor-card h5 {
            font-size: 20px;
            margin-bottom: 8px;
            color: white;
        }

        .instructor-card p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
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

            .course-layout {
                grid-template-columns: 1fr;
            }

            .course-stats {
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
                <a href="my-courses.php" class="nav-item">📖 My Courses</a>
                <a href="my-certificates.php" class="nav-item">🏆 Certificates</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                <?php if (!$course['is_enrolled']): ?>
                    <a href="enroll.php?id=<?php echo $course_id; ?>" class="btn btn-primary">📝 Enroll Now</a>
                <?php endif; ?>
            </div>
            
            <?php display_message(); ?>
            
            <?php if ($course['is_enrolled'] && isset($progress)): ?>
            <div class="progress-card">
                <h4>Your Progress</h4>
                <div class="progress">
                    <div class="progress-bar <?php echo $progress['is_completed'] ? 'complete' : ''; ?>" style="width: <?php echo $progress['progress_percentage']; ?>%">
                        <?php echo number_format($progress['progress_percentage'], 0); ?>%
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="course-layout">
                <div>
                    <div class="card">
                        <div>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($course['category']); ?></span>
                            <span class="badge badge-secondary"><?php echo ucfirst($course['difficulty']); ?></span>
                        </div>
                        
                        <h3>About This Course</h3>
                        <div class="course-description">
                            <?php echo nl2br(htmlspecialchars($course['long_description'])); ?>
                        </div>
                        
                        <div class="course-stats">
                            <div class="stat-item">
                                <strong>📚 Lessons</strong>
                                <span><?php echo count($lessons); ?></span>
                            </div>
                            <div class="stat-item">
                                <strong>❓ Quizzes</strong>
                                <span><?php echo count($quizzes); ?></span>
                            </div>
                            <div class="stat-item">
                                <strong>⏱️ Duration</strong>
                                <span><?php echo format_duration($course['estimated_duration']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <h3>Course Curriculum</h3>
                        <?php if (!empty($lessons)): ?>
                            <div class="lesson-list">
                                <?php foreach ($lessons as $lesson): ?>
                                <div class="lesson-item">
                                    <div class="lesson-info">
                                        <h5><?php echo htmlspecialchars($lesson['title']); ?></h5>
                                        <small>⏱️ <?php echo $lesson['estimated_duration']; ?> min</small>
                                    </div>
                                    <?php if ($course['is_enrolled']): ?>
                                        <a href="lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #a0aec0; text-align: center; padding: 40px 0;">No lessons available yet.</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($course['is_enrolled'] && !empty($quizzes)): ?>
                    <div class="card">
                        <h3>Course Quizzes</h3>
                        <div class="lesson-list">
                            <?php foreach ($quizzes as $quiz): ?>
                            <div class="lesson-item">
                                <div class="lesson-info">
                                    <h5><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                    <small>📝 <?php echo $quiz['passing_score']; ?>% passing score<?php if ($quiz['time_limit']): ?> • ⏱️ <?php echo $quiz['time_limit']; ?> min<?php endif; ?></small>
                                </div>
                                <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary btn-sm">Take Quiz</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <div class="instructor-card">
                        <h4>👨‍🏫 Instructor</h4>
                        <h5><?php echo htmlspecialchars($course['instructor_name']); ?></h5>
                        <p>Course Instructor</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>