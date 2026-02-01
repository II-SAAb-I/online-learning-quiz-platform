<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();
$course_id = intval($_GET['course_id'] ?? 0);

if (!$course_id) {
    set_message('error', 'Invalid course');
    redirect('courses.php');
}

if (!owns_resource($conn, 'courses', $course_id, 'created_by', $user_id)) {
    set_message('error', 'Access denied');
    redirect('courses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token');
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $passing_score = intval($_POST['passing_score'] ?? 70);
        $time_limit = intval($_POST['time_limit'] ?? 0);
        $max_attempts = intval($_POST['max_attempts'] ?? 0);
        $show_correct_answers = isset($_POST['show_correct_answers']) ? 1 : 0;
        
        if (empty($title)) {
            set_message('error', 'Quiz title is required');
        } else {
            $time_limit = $time_limit > 0 ? $time_limit : null;
            $max_attempts = $max_attempts > 0 ? $max_attempts : null;
            
            $sql = "INSERT INTO quizzes (course_id, title, description, passing_score, time_limit, max_attempts, show_correct_answers) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $result = db_query($conn, $sql, "issiiii", [$course_id, $title, $description, $passing_score, $time_limit, $max_attempts, $show_correct_answers]);
            
            if ($result) {
                $quiz_id = db_insert_id($conn);
                set_message('success', 'Quiz created successfully');
                redirect('questions.php?quiz_id=' . $quiz_id);
            } else {
                set_message('error', 'Failed to create quiz');
            }
        }
    }
}

$page_title = 'Create Quiz';
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
                <a href="courses.php" class="nav-item active">📚 My Courses</a>
                <a href="course-create.php" class="nav-item">➕ Create Course</a>
                <a href="students.php" class="nav-item">👥 Students</a>
                <a href="analytics.php" class="nav-item">📈 Analytics</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="quizzes.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">← Back to Quizzes</a>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label for="title">Quiz Title *</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="passing_score">Passing Score (%)</label>
                                    <input type="number" id="passing_score" name="passing_score" class="form-control" value="70" min="0" max="100" required>
                                </div>
                            </div>
                            
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="time_limit">Time Limit (minutes)</label>
                                    <input type="number" id="time_limit" name="time_limit" class="form-control" value="0" min="0">
                                    <small class="form-text">0 = No time limit</small>
                                </div>
                            </div>
                            
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="max_attempts">Max Attempts</label>
                                    <input type="number" id="max_attempts" name="max_attempts" class="form-control" value="0" min="0">
                                    <small class="form-text">0 = Unlimited</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="show_correct_answers" name="show_correct_answers" checked>
                            <label for="show_correct_answers">Show correct answers after submission</label>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Create Quiz</button>
                            <a href="quizzes.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>