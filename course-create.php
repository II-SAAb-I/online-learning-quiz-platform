<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token');
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $short_description = sanitize($_POST['short_description'] ?? '');
        $long_description = sanitize($_POST['long_description'] ?? '');
        $category = sanitize($_POST['category'] ?? '');
        $difficulty = sanitize($_POST['difficulty'] ?? 'beginner');
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        
        if (empty($title) || empty($short_description) || empty($category)) {
            set_message('error', 'Please fill all required fields');
        } else {
            $thumbnail = null;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                $upload_result = upload_file($_FILES['thumbnail'], UPLOAD_PATH . '/courses', ALLOWED_IMAGE_TYPES);
                if ($upload_result['success']) {
                    $thumbnail = $upload_result['filename'];
                }
            }
            
            $sql = "INSERT INTO courses (title, short_description, long_description, category, difficulty, thumbnail, created_by, is_published) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $result = db_query($conn, $sql, "ssssssii", [$title, $short_description, $long_description, $category, $difficulty, $thumbnail, $user_id, $is_published]);
            
            if ($result) {
                $course_id = db_insert_id($conn);
                set_message('success', 'Course created successfully');
                redirect('lessons.php?course_id=' . $course_id);
            } else {
                set_message('error', 'Failed to create course');
            }
        }
    }
}

$page_title = 'Create Course';
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
                <h2>👨‍🏫 Instructor</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">📊 Dashboard</a>
                <a href="courses.php" class="nav-item">📚 My Courses</a>
                <a href="course-create.php" class="nav-item active">➕ Create Course</a>
                <a href="students.php" class="nav-item">👥 Students</a>
                <a href="analytics.php" class="nav-item">📈 Analytics</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="courses.php" class="btn btn-secondary">← Back to Courses</a>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label for="title">Course Title *</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="short_description">Short Description *</label>
                            <input type="text" id="short_description" name="short_description" class="form-control" maxlength="500" required>
                            <small class="form-text">Brief overview (max 500 characters)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="long_description">Full Description</label>
                            <textarea id="long_description" name="long_description" class="form-control" rows="6"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="category">Category *</label>
                                    <input type="text" id="category" name="category" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="difficulty">Difficulty Level *</label>
                                    <select id="difficulty" name="difficulty" class="form-control" required>
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="thumbnail">Course Thumbnail</label>
                            <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
                            <small class="form-text">Recommended size: 800x450px</small>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="is_published" name="is_published">
                            <label for="is_published">Publish course immediately</label>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Create Course</button>
                            <a href="courses.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>