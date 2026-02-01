<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token');
    } else {
        set_message('info', 'Settings feature is under development. Configuration changes should be made in config/config.php file.');
    }
}

$db_stats_sql = "SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM courses) as total_courses,
    (SELECT COUNT(*) FROM lessons) as total_lessons,
    (SELECT COUNT(*) FROM quizzes) as total_quizzes,
    (SELECT COUNT(*) FROM questions) as total_questions,
    (SELECT COUNT(*) FROM enrollments) as total_enrollments,
    (SELECT COUNT(*) FROM quiz_attempts) as total_attempts,
    (SELECT COUNT(*) FROM certificates) as total_certificates";

$db_stats = db_fetch_assoc(db_query($conn, $db_stats_sql));

$page_title = 'System Settings';
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
                <a href="dashboard.php" class="nav-item">
                    📊 Dashboard
                </a>
                <a href="users.php" class="nav-item">
                    👥 Users
                </a>
                <a href="courses.php" class="nav-item">
                    📚 Courses
                </a>
                <a href="categories.php" class="nav-item">
                    🏷️ Categories
                </a>
                <a href="analytics.php" class="nav-item">
                    📈 Analytics
                </a>
                <a href="reports.php" class="nav-item">
                    📄 Reports
                </a>
                <a href="settings.php" class="nav-item active">
                    ⚙️ Settings
                </a>
                <a href="../logout.php" class="nav-item">
                    🚪 Logout
                </a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo $page_title; ?></h1>
            </div>
            
            <?php display_message(); ?>
            
            <div class="row mb-4">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>System Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th>Platform Name:</th>
                                    <td><?php echo SITE_NAME; ?></td>
                                </tr>
                                <tr>
                                    <th>Site URL:</th>
                                    <td><?php echo SITE_URL; ?></td>
                                </tr>
                                <tr>
                                    <th>PHP Version:</th>
                                    <td><?php echo phpversion(); ?></td>
                                </tr>
                                <tr>
                                    <th>Database:</th>
                                    <td><?php echo DB_NAME; ?></td>
                                </tr>
     
                                <tr>
                                    <th>Session Timeout:</th>
                                    <td><?php echo SESSION_TIMEOUT / 60; ?> minutes</td>
                                </tr>
                                <tr>
                                    <th>Max File Size:</th>
                                    <td><?php echo format_file_size(MAX_FILE_SIZE); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Database Statistics</h3>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th>Total Users:</th>
                                    <td><?php echo number_format($db_stats['total_users']); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Courses:</th>
                                    <td><?php echo number_format($db_stats['total_courses']); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Lessons:</th>
                                    <td><?php echo number_format($db_stats['total_lessons']); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Quizzes:</th>
                                    <td><?php echo number_format($db_stats['total_quizzes']); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Questions:</th>
                                    <td><?php echo number_format($db_stats['total_questions']); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Enrollments:</th>
                                    <td><?php echo number_format($db_stats['total_enrollments']); ?></td>
                                </tr>
                                <tr>
                                    <th>Quiz Attempts:</th>
                                    <td><?php echo number_format($db_stats['total_attempts']); ?></td>
                                </tr>
                                <tr>
                                    <th>Certificates Issued:</th>
                                    <td><?php echo number_format($db_stats['total_certificates']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Feature Settings</h3>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th>Certificates:</th>
                                    <td>
                                        <?php if (FEATURE_CERTIFICATES): ?>
                                            <span class="badge badge-success">Enabled</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Disabled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Course Reviews:</th>
                                    <td>
                                        <?php if (FEATURE_COURSE_REVIEWS): ?>
                                            <span class="badge badge-success">Enabled</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Disabled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Discussion Forum:</th>
                                    <td>
                                        <?php if (FEATURE_DISCUSSION_FORUM): ?>
                                            <span class="badge badge-success">Enabled</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Disabled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Assignments:</th>
                                    <td>
                                        <?php if (FEATURE_ASSIGNMENTS): ?>
                                            <span class="badge badge-success">Enabled</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Disabled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Security Settings</h3>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th>Min Password Length:</th>
                                    <td><?php echo MIN_PASSWORD_LENGTH; ?> characters</td>
                                </tr>
                                <tr>
                                    <th>Require Number:</th>
                                    <td>
                                        <?php if (REQUIRE_NUMBER): ?>
                                            <span class="badge badge-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Require Uppercase:</th>
                                    <td>
                                        <?php if (REQUIRE_UPPERCASE): ?>
                                            <span class="badge badge-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Require Special Char:</th>
                                    <td>
                                        <?php if (REQUIRE_SPECIAL_CHAR): ?>
                                            <span class="badge badge-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>CSRF Token Expiry:</th>
                                    <td><?php echo CSRF_TOKEN_EXPIRY / 60; ?> minutes</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Configuration Note</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>💡 Configuration Management</strong><br>
                        To modify system settings, please edit the <code>/config/config.php</code> file directly.
                        Settings include:
                        <ul>
                            <li>Site name, URL, and contact information</li>
                            <li>Upload limits and allowed file types</li>
                            <li>Feature flags (enable/disable features)</li>
                            <li>Security requirements</li>
                            <li>Session and timeout settings</li>
                            <li>Email configuration (if using email features)</li>
                        </ul>
                        After making changes, ensure to test thoroughly before deploying to production.
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>