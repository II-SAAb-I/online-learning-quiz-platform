<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$user_id = get_user_id();

$sql = "SELECT cert.*, c.title as course_title, c.category
        FROM certificates cert
        JOIN courses c ON cert.course_id = c.id
        WHERE cert.user_id = ?
        ORDER BY cert.generated_at DESC";

$certificates = db_fetch_all(db_query($conn, $sql, "i", [$user_id]));

$completed_sql = "SELECT c.id, c.title
                  FROM enrollments e
                  JOIN courses c ON e.course_id = c.id
                  WHERE e.user_id = ? AND e.is_completed = 1
                  AND NOT EXISTS (SELECT 1 FROM certificates WHERE course_id = c.id AND user_id = ?)";

$completed_courses = db_fetch_all(db_query($conn, $completed_sql, "ii", [$user_id, $user_id]));

$page_title = 'My Certificates';
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

        .content-header h1 {
            font-size: clamp(28px, 3vw, 36px);
            font-weight: 800;
            color: #4a5568;
            letter-spacing: -0.5px;
            margin-bottom: 40px;
        }

        .alert {
            padding: 20px 24px;
            border-radius: 16px;
            margin-bottom: 32px;
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(134, 195, 168, 0.15) 0%, rgba(134, 195, 168, 0.2) 100%);
            border: 2px solid #86c3a8;
            color: #4a7c59;
        }

        .cert-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 32px;
            margin-bottom: 48px;
        }

        .cert-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            border: 2px solid #f7dfd4;
        }

        .cert-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15);
            border-color: #d4a5a5;
        }

        .cert-preview {
            text-align: center;
            padding: 48px 32px;
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .cert-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="white" opacity="0.1"/></svg>');
            opacity: 0.3;
        }

        .cert-icon {
            font-size: 56px;
            margin-bottom: 20px;
            position: relative;
        }

        .cert-preview h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            color: white;
            position: relative;
        }

        .cert-category {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            position: relative;
        }

        .cert-info {
            padding: 28px;
        }

        .cert-detail {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .cert-label {
            font-size: 12px;
            font-weight: 700;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .cert-value {
            font-size: 15px;
            color: #4a5568;
            font-weight: 600;
        }

        .cert-code {
            background: #f7dfd4;
            padding: 10px 16px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #9c6b65;
            font-weight: 700;
            border: 2px solid #f0d5c9;
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
            width: 100%;
            margin-top: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 165, 165, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #86c3a8 0%, #6fba94 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(134, 195, 168, 0.4);
        }

        .empty-state {
            background: white;
            border-radius: 24px;
            padding: 80px 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
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

        .pending-section {
            background: white;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
        }

        .pending-section h2 {
            font-size: 22px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 24px;
        }

        .pending-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        .pending-table thead th {
            font-size: 12px;
            font-weight: 700;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 2px solid #f7dfd4;
        }

        .pending-table tbody tr {
            background: #fef8f5;
            transition: all 0.2s ease;
        }

        .pending-table tbody tr:hover {
            background: #fef5f1;
            transform: translateX(4px);
        }

        .pending-table tbody td {
            padding: 20px 16px;
            border-top: 1px solid #f7dfd4;
            border-bottom: 1px solid #f7dfd4;
        }

        .pending-table tbody td:first-child {
            border-left: 1px solid #f7dfd4;
            border-radius: 12px 0 0 12px;
            font-weight: 600;
            color: #4a5568;
        }

        .pending-table tbody td:last-child {
            border-right: 1px solid #f7dfd4;
            border-radius: 0 12px 12px 0;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
                padding: 24px;
            }

            .cert-grid {
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
                <a href="my-certificates.php" class="nav-item active">🏆 Certificates</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1>🏆 My Certificates</h1>
            </div>
            
            <?php display_message(); ?>
            
            <?php if (!empty($completed_courses)): ?>
            <div class="alert alert-info">
                <span style="font-size: 24px;">🎉</span>
                <span>You have <strong><?php echo count($completed_courses); ?></strong> completed course<?php echo count($completed_courses) !== 1 ? 's' : ''; ?> ready for certificate generation!</span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($certificates)): ?>
                <div class="cert-grid">
                    <?php foreach ($certificates as $cert): ?>
                    <div class="cert-card">
                        <div class="cert-preview">
                            <div class="cert-icon">🏆</div>
                            <h3><?php echo htmlspecialchars($cert['course_title']); ?></h3>
                            <p class="cert-category"><?php echo htmlspecialchars($cert['category']); ?></p>
                        </div>
                        
                        <div class="cert-info">
                            <div class="cert-detail">
                                <span class="cert-label">Issued Date</span>
                                <span class="cert-value"><?php echo format_date($cert['generated_at']); ?></span>
                            </div>
                            
                            <div class="cert-detail">
                                <span class="cert-label">Verification Code</span>
                                <div class="cert-code"><?php echo htmlspecialchars($cert['verification_code']); ?></div>
                            </div>
                            
                            <a href="download-certificate.php?id=<?php echo $cert['id']; ?>" class="btn btn-primary">
                                📥 Download Certificate
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🏆</div>
                    <h3>No certificates yet</h3>
                    <p>Complete courses to earn certificates</p>
                    <a href="my-courses.php" class="btn btn-primary" style="display: inline-block; width: auto;">View My Courses</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($completed_courses)): ?>
            <div class="pending-section">
                <h2>Generate Certificates</h2>
                <table class="pending-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completed_courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td style="text-align: right;">
                                <a href="download-certificate.php?course_id=<?php echo $course['id']; ?>" class="btn btn-success" style="padding: 10px 20px; font-size: 14px;">
                                    ✨ Generate Certificate
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>