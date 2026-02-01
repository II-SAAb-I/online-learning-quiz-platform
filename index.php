<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';

$sql = "SELECT c.*, u.full_name as instructor_name, 
        COUNT(DISTINCT e.id) as enrollment_count,
        COUNT(DISTINCT l.id) as lesson_count
        FROM courses c
        LEFT JOIN users u ON c.created_by = u.id
        LEFT JOIN enrollments e ON c.id = e.course_id
        LEFT JOIN lessons l ON c.id = l.course_id
        WHERE c.is_published = 1
        GROUP BY c.id
        ORDER BY c.created_at DESC
        LIMIT 6";

$featured_courses = db_fetch_all(db_query($conn, $sql));

$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'student') as total_students,
    (SELECT COUNT(*) FROM courses WHERE is_published = 1) as total_courses,
    (SELECT COUNT(*) FROM users WHERE role = 'instructor') as total_instructors,
    (SELECT COUNT(*) FROM certificates) as total_certificates";

$stats = db_fetch_assoc(db_query($conn, $stats_sql));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo SITE_TAGLINE; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: #fef8f5;
            color: #2d3748;
        }
        .navbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .navbar-content { display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand { font-size: 24px; font-weight: 700; color: #d4a5a5; text-decoration: none; }
        .navbar-buttons { display: flex; gap: 12px; }
        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-outline { color: #d4a5a5; border: 2px solid #d4a5a5; background: transparent; }
        .btn-outline:hover { background: #d4a5a5; color: white; }
        .btn-primary {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(212, 165, 165, 0.4); }
        .hero {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
            padding: 80px 24px;
            text-align: center;
        }
        .hero-title { font-size: clamp(32px, 5vw, 56px); font-weight: 800; margin-bottom: 20px; }
        .hero-subtitle { font-size: clamp(16px, 2vw, 20px); margin-bottom: 40px; opacity: 0.95; }
        .hero-buttons { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
        .btn-white { background: white; color: #d4a5a5; padding: 16px 32px; }
        .btn-lg { padding: 16px 32px; font-size: 16px; }
        .stats-section { padding: 0 24px 80px; margin-top: -60px; z-index: 10; position: relative; }
        .stats-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            padding: 48px 32px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; text-align: center; }
        .stat-number { font-size: clamp(32px, 4vw, 48px); font-weight: 800; color: #d4a5a5; }
        .stat-label { font-size: 15px; color: #718096; font-weight: 500; }
        .courses-section { padding: 80px 24px; }
        .section-header { text-align: center; margin-bottom: 56px; }
        .section-title { font-size: clamp(28px, 3vw, 40px); font-weight: 800; color: #4a5568; }
        .courses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 32px; }
        .course-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            border: 2px solid #f7dfd4;
        }
        .course-card:hover { transform: translateY(-12px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15); border-color: #d4a5a5; }
        .course-image { width: 100%; height: 200px; background: linear-gradient(135deg, #d4a5a5, #f7dfd4); }
        .course-content { padding: 24px; }
        .course-category {
            display: inline-block;
            padding: 6px 12px;
            background: rgba(212, 165, 165, 0.15);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            color: #d4a5a5;
            margin-bottom: 12px;
        }
        .course-title { font-size: 20px; font-weight: 700; margin-bottom: 12px; color: #4a5568; }
        .course-description { font-size: 14px; color: #718096; margin-bottom: 20px; }
        .course-meta {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #a0aec0;
            padding-top: 16px;
            border-top: 1px solid #f7dfd4;
            margin-bottom: 16px;
        }
        .footer { background: #4a5568; color: white; padding: 48px 24px 24px; text-align: center; }
        @media (max-width: 768px) {
            .courses-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="index.php" class="navbar-brand">🎓 LearnHub</a>
                <div class="navbar-buttons">
                    <?php if (is_logged_in()): ?>
                        <a href="<?php echo get_dashboard_url(); ?>" class="btn btn-outline">Dashboard</a>
                        <a href="logout.php" class="btn btn-primary">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Login</a>
                        <a href="register.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Master New Skills, Anytime, Anywhere</h1>
            <p class="hero-subtitle">Join thousands of learners and start your journey to success today</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-primary btn-lg">Get Started Free</a>
                <a href="login.php" class="btn btn-white btn-lg">Sign In</a>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="stats-card">
            <div class="stats-grid">
                <div><span class="stat-number"><?php echo number_format($stats['total_students'] ?? 0); ?>+</span><div class="stat-label">Active Students</div></div>
                <div><span class="stat-number"><?php echo number_format($stats['total_courses'] ?? 0); ?>+</span><div class="stat-label">Online Courses</div></div>
                <div><span class="stat-number"><?php echo number_format($stats['total_instructors'] ?? 0); ?>+</span><div class="stat-label">Expert Instructors</div></div>
                <div><span class="stat-number"><?php echo number_format($stats['total_certificates'] ?? 0); ?>+</span><div class="stat-label">Certificates Issued</div></div>
            </div>
        </div>
    </section>

    <section class="courses-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Courses</h2>
            </div>
            
            <?php if (!empty($featured_courses)): ?>
                <div class="courses-grid">
                    <?php foreach ($featured_courses as $course): ?>
                    <div class="course-card">
                        <div class="course-image"></div>
                        <div class="course-content">
                            <span class="course-category"><?php echo htmlspecialchars($course['category']); ?></span>
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="course-description"><?php echo htmlspecialchars(truncate($course['short_description'], 100)); ?></p>
                            <div class="course-meta">
                                <small>👨‍🏫 <?php echo htmlspecialchars($course['instructor_name']); ?></small>
                                <small>📚 <?php echo $course['lesson_count']; ?> lessons</small>
                            </div>
                            <a href="<?php echo is_logged_in() ? 'student/course-detail.php?id=' . $course['id'] : 'register.php'; ?>" class="btn btn-primary" style="width: 100%;">
                                <?php echo is_logged_in() ? 'View Course' : 'Sign Up to Enroll'; ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>