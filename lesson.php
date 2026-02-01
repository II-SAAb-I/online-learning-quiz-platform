<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$lesson_id = intval($_GET['id'] ?? 0);
$user_id = get_user_id();

if (!$lesson_id) {
    set_message('error', 'Invalid lesson');
    redirect('my-courses.php');
}

$sql = "SELECT l.*, c.id as course_id, c.title as course_title
        FROM lessons l
        JOIN courses c ON l.course_id = c.id
        WHERE l.id = ?";

$result = db_query($conn, $sql, "i", [$lesson_id]);

if (!$result || db_num_rows($result) == 0) {
    set_message('error', 'Lesson not found');
    redirect('my-courses.php');
}

$lesson = db_fetch_assoc($result);

if (!can_access_course($conn, $lesson['course_id'], $user_id)) {
    set_message('error', 'You must enroll in this course first');
    redirect('course-detail.php?id=' . $lesson['course_id']);
}

$completed_sql = "SELECT id FROM lesson_completion WHERE lesson_id = ? AND user_id = ?";
$completed_result = db_query($conn, $completed_sql, "ii", [$lesson_id, $user_id]);
$is_completed = db_num_rows($completed_result) > 0;

$all_lessons_sql = "SELECT id, title FROM lessons WHERE course_id = ? ORDER BY lesson_order";
$all_lessons = db_fetch_all(db_query($conn, $all_lessons_sql, "i", [$lesson['course_id']]));

$page_title = $lesson['title'];
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
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
            font-size: 18px;
            font-weight: 700;
            color: white;
        }
        .sidebar-nav { padding: 24px 16px; }
        .nav-section-title {
            margin: 1rem 0 0.5rem;
            padding: 0 1rem;
            color: rgba(255,255,255,0.6);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 4px;
            transition: all 0.3s ease;
            font-size: 14px;
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
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 800;
            color: #4a5568;
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
        }
        .btn-success {
            background: linear-gradient(135deg, #86c3a8 0%, #6fba94 100%);
            color: white;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(134, 195, 168, 0.4);
        }
        .btn-primary {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 165, 165, 0.4);
        }
        .badge {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
        }
        .badge-success {
            background: linear-gradient(135deg, rgba(134, 195, 168, 0.2) 0%, rgba(134, 195, 168, 0.3) 100%);
            color: #4a7c59;
        }
        .card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 16px;
            margin-bottom: 32px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
            border-radius: 16px;
        }
        .lesson-content {
            line-height: 1.8;
            color: #4a5568;
            font-size: 16px;
        }
        .lesson-content p {
            margin-bottom: 16px;
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
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>📚 <?php echo htmlspecialchars(truncate($lesson['course_title'], 25)); ?></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="course-detail.php?id=<?php echo $lesson['course_id']; ?>" class="nav-item">← Back to Course</a>
                <div class="nav-section-title">LESSONS</div>
                <?php foreach ($all_lessons as $l): ?>
                <a href="lesson.php?id=<?php echo $l['id']; ?>" class="nav-item <?php echo $l['id'] == $lesson_id ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars(truncate($l['title'], 30)); ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
                <?php if (!$is_completed): ?>
                    <a href="complete-lesson.php?id=<?php echo $lesson_id; ?>" class="btn btn-success" onclick="return confirm('Mark this lesson as complete?')">✓ Mark Complete</a>
                <?php else: ?>
                    <span class="badge badge-success">✓ Completed</span>
                <?php endif; ?>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <?php
                // Debug: Check if video_url exists
                if (isset($lesson['video_url']) && !empty($lesson['video_url'])) {
                    $embed_url = convert_to_embed_url($lesson['video_url']);
                    echo "<!-- Debug: Original URL: " . htmlspecialchars($lesson['video_url']) . " -->\n";
                    echo "<!-- Debug: Embed URL: " . htmlspecialchars($embed_url) . " -->\n";
                ?>
                <div class="video-container">
                    <iframe
                        src="<?php echo htmlspecialchars($embed_url); ?>"
                        allowfullscreen>
                    </iframe>
                </div>
                <?php } else { ?>
                <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px;">
                    <p><strong>Debug Info:</strong> No video URL found for this lesson.</p>
                    <p>Lesson ID: <?php echo $lesson_id; ?></p>
                    <p>Video URL in database: <?php echo isset($lesson['video_url']) ? htmlspecialchars($lesson['video_url']) : 'Not set'; ?></p>
                </div>
                <?php } ?>
                
                <div class="lesson-content">
                    <?php echo nl2br(htmlspecialchars($lesson['content'])); ?>
                </div>
                
                <?php if ($lesson['file_attachment']): ?>
                <div style="margin-top: 32px; padding-top: 32px; border-top: 2px solid #f7dfd4;">
                    <a href="<?php echo UPLOADS_URL . '/lessons/' . htmlspecialchars($lesson['file_attachment']); ?>" class="btn btn-primary" download>
                        📥 Download Attachment
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>