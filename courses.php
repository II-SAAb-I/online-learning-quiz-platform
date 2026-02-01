<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$user_id = get_user_id();
$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');
$difficulty = sanitize($_GET['difficulty'] ?? '');

$sql = "SELECT c.*, u.full_name as instructor_name,
        COUNT(DISTINCT e.id) as enrollment_count,
        COUNT(DISTINCT l.id) as lesson_count,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND user_id = ?) as is_enrolled
        FROM courses c
        JOIN users u ON c.created_by = u.id
        LEFT JOIN enrollments e ON c.id = e.course_id
        LEFT JOIN lessons l ON c.id = l.course_id
        WHERE c.is_published = 1";

$params = [$user_id];
$types = "i";

if (!empty($search)) {
    $sql .= " AND (c.title LIKE ? OR c.short_description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($category)) {
    $sql .= " AND c.category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($difficulty)) {
    $sql .= " AND c.difficulty = ?";
    $params[] = $difficulty;
    $types .= "s";
}

$sql .= " GROUP BY c.id ORDER BY c.created_at DESC";

$result = db_query($conn, $sql, $types, $params);
$courses = db_fetch_all($result);

$categories_sql = "SELECT DISTINCT category FROM courses WHERE is_published = 1 ORDER BY category";
$categories = db_fetch_all(db_query($conn, $categories_sql));

$page_title = 'Browse Courses';
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
        .filter-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
        }
        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 16px;
            align-items: end;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            font-size: 15px;
            border: 2px solid #f7dfd4;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #fef8f5;
        }
        .form-control:focus {
            outline: none;
            border-color: #d4a5a5;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(212, 165, 165, 0.1);
        }
        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%239c6b65' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
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
        .btn-success {
            background: linear-gradient(135deg, #86c3a8 0%, #6fba94 100%);
            color: white;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(134, 195, 168, 0.4);
        }
        .btn-block { width: 100%; }
        .results-count {
            font-size: 15px;
            color: #718096;
            margin-bottom: 24px;
            font-weight: 500;
        }
        .results-count strong {
            color: #4a5568;
            font-weight: 700;
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
            border: 2px solid #f7dfd4;
            display: flex;
            flex-direction: column;
        }
        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15);
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
        .badge-group {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-primary {
            background: linear-gradient(135deg, rgba(212, 165, 165, 0.15) 0%, rgba(199, 154, 148, 0.15) 100%);
            color: #9c6b65;
        }
        .badge-secondary {
            background: rgba(113, 128, 150, 0.15);
            color: #4a5568;
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
            flex: 1;
        }
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 2px solid #f7dfd4;
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
        @media (max-width: 1200px) {
            .filter-form {
                grid-template-columns: 1fr 1fr;
            }
            .course-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; padding: 24px; }
        }
        @media (max-width: 768px) {
            .filter-form { grid-template-columns: 1fr; }
            .filter-card { padding: 24px 20px; }
            .course-grid { grid-template-columns: 1fr; }
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
                <a href="courses.php" class="nav-item active">📚 Browse Courses</a>
                <a href="my-courses.php" class="nav-item">📖 My Courses</a>
                <a href="my-certificates.php" class="nav-item">🏆 Certificates</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1>📚 Browse Courses</h1>
            </div>
            
            <?php display_message(); ?>
            
            <div class="filter-card">
                <form method="GET" action="" class="filter-form">
                    <div class="form-group">
                        <label>Search Courses</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by title or description..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Difficulty</label>
                        <select name="difficulty" class="form-control">
                            <option value="">All Levels</option>
                            <option value="beginner" <?php echo $difficulty === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="intermediate" <?php echo $difficulty === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="advanced" <?php echo $difficulty === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        🔍 Search
                    </button>
                </form>
            </div>
            
            <?php if (!empty($courses)): ?>
                <div class="results-count">
                    Found <strong><?php echo count($courses); ?></strong> course<?php echo count($courses) !== 1 ? 's' : ''; ?>
                </div>
                
                <div class="course-grid">
                    <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <?php if ($course['thumbnail']): ?>
                            <img src="<?php echo UPLOADS_URL . '/courses/' . htmlspecialchars($course['thumbnail']); ?>" class="course-image" alt="Course">
                        <?php else: ?>
                            <div class="course-image"></div>
                        <?php endif; ?>
                        
                        <div class="course-body">
                            <div class="badge-group">
                                <span class="badge badge-primary"><?php echo htmlspecialchars($course['category']); ?></span>
                                <span class="badge badge-secondary"><?php echo ucfirst($course['difficulty']); ?></span>
                            </div>
                            
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="course-description"><?php echo htmlspecialchars(truncate($course['short_description'], 100)); ?></p>
                            
                            <div class="course-meta">
                                <span>👨‍🏫 <?php echo htmlspecialchars($course['instructor_name']); ?></span>
                                <span>📚 <?php echo $course['lesson_count']; ?> lessons</span>
                            </div>
                            
                            <?php if ($course['is_enrolled']): ?>
                                <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-success btn-block">
                                    ✅ Continue Learning
                                </a>
                            <?php else: ?>
                                <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-block">
                                    👁️ View Details
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🔍</div>
                    <h3>No Courses Found</h3>
                    <p>We couldn't find any courses matching your search criteria.<br>Try adjusting your filters or search terms.</p>
                    <a href="courses.php" class="btn btn-primary">View All Courses</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>