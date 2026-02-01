<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();
$quiz_id = intval($_GET['quiz_id'] ?? 0);

if (!$quiz_id) {
    set_message('error', 'Invalid quiz');
    redirect('courses.php');
}

$sql = "SELECT q.*, c.created_by, c.id as course_id FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ?";
$result = db_query($conn, $sql, "i", [$quiz_id]);
$quiz = db_fetch_assoc($result);

if (!$quiz || !owns_resource($conn, 'courses', $quiz['course_id'], 'created_by', $user_id)) {
    set_message('error', 'Access denied');
    redirect('courses.php');
}

$questions_sql = "SELECT q.*, COUNT(a.id) as answer_count
                  FROM questions q
                  LEFT JOIN answers a ON q.id = a.question_id
                  WHERE q.quiz_id = ?
                  GROUP BY q.id
                  ORDER BY q.question_order";
$questions = db_fetch_all(db_query($conn, $questions_sql, "i", [$quiz_id]));

$page_title = 'Manage Questions';
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
                <h1><?php echo $page_title; ?>: <?php echo htmlspecialchars($quiz['title']); ?></h1>
                <div>
                    <a href="question-create.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary">+ Add Question</a>
                    <a href="quizzes.php?course_id=<?php echo $quiz['course_id']; ?>" class="btn btn-secondary">← Back to Quizzes</a>
                </div>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($questions)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Question</th>
                                <th>Type</th>
                                <th>Answers</th>
                                <th>Points</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questions as $question): ?>
                            <tr>
                                <td><?php echo $question['question_order']; ?></td>
                                <td><?php echo htmlspecialchars(truncate($question['question_text'], 60)); ?></td>
                                <td><span class="badge badge-primary"><?php echo strtoupper($question['question_type']); ?></span></td>
                                <td><?php echo $question['answer_count']; ?></td>
                                <td><?php echo $question['points']; ?></td>
                                <td>
                                    <a href="question-edit.php?id=<?php echo $question['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="question-delete.php?id=<?php echo $question['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-center text-muted">No questions yet. Add your first question!</p>
                    <div class="text-center">
                        <a href="question-create.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary">Add First Question</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>