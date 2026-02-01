<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();
$question_id = intval($_GET['id'] ?? 0);

if (!$question_id) {
    set_message('error', 'Invalid question');
    redirect('courses.php');
}

$sql = "SELECT q.*, qu.id as quiz_id, qu.course_id, c.created_by
        FROM questions q
        JOIN quizzes qu ON q.quiz_id = qu.id
        JOIN courses c ON qu.course_id = c.id
        WHERE q.id = ?";
$result = db_query($conn, $sql, "i", [$question_id]);
$question = db_fetch_assoc($result);

if (!$question || !owns_resource($conn, 'courses', $question['course_id'] ?? 0, 'created_by', $user_id)) {
    set_message('error', 'Access denied');
    redirect('courses.php');
}

$answers_sql = "SELECT * FROM answers WHERE question_id = ? ORDER BY answer_order";
$answers = db_fetch_all(db_query($conn, $answers_sql, "i", [$question_id]));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token');
    } else {
        $question_text = sanitize($_POST['question_text'] ?? '');
        $points = intval($_POST['points'] ?? 1);
        $explanation = sanitize($_POST['explanation'] ?? '');
        $question_order = intval($_POST['question_order'] ?? 1);
        
        if (empty($question_text)) {
            set_message('error', 'Question text is required');
        } else {
            $sql = "UPDATE questions SET question_text = ?, points = ?, explanation = ?, question_order = ? WHERE id = ?";
            $result = db_query($conn, $sql, "sisii", [$question_text, $points, $explanation, $question_order, $question_id]);
            
            if ($result) {
                $answer_ids = $_POST['answer_ids'] ?? [];
                $answer_texts = $_POST['answer_texts'] ?? [];
                $correct_answers = $_POST['correct'] ?? [];
                
                foreach ($answer_ids as $index => $answer_id) {
                    $answer_text = sanitize($answer_texts[$index] ?? '');
                    $is_correct = in_array($answer_id, $correct_answers) ? 1 : 0;
                    
                    if (!empty($answer_text)) {
                        $ans_sql = "UPDATE answers SET answer_text = ?, is_correct = ? WHERE id = ?";
                        db_query($conn, $ans_sql, "sii", [$answer_text, $is_correct, $answer_id]);
                    }
                }
                
                set_message('success', 'Question updated successfully');
                redirect('questions.php?quiz_id=' . $question['quiz_id']);
            } else {
                set_message('error', 'Failed to update question');
            }
        }
    }
}

$page_title = 'Edit Question';
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
                <a href="questions.php?quiz_id=<?php echo $question['quiz_id']; ?>" class="btn btn-secondary">← Back to Questions</a>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label for="question_text">Question Text *</label>
                            <textarea id="question_text" name="question_text" class="form-control" rows="3" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="explanation">Explanation (Optional)</label>
                            <textarea id="explanation" name="explanation" class="form-control" rows="2"><?php echo htmlspecialchars($question['explanation']); ?></textarea>
                            <small class="form-text">Shown to students after they answer</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="points">Points</label>
                                    <input type="number" id="points" name="points" class="form-control" value="<?php echo $question['points']; ?>" min="1" required>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="question_order">Question Order</label>
                                    <input type="number" id="question_order" name="question_order" class="form-control" value="<?php echo $question['question_order']; ?>" min="1" required>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="mt-4">Answers</h4>
                        <p class="text-muted">Question Type: <strong><?php echo strtoupper($question['question_type']); ?></strong></p>
                        
                        <?php foreach ($answers as $index => $answer): ?>
                        <div class="card mb-2">
                            <div class="card-body">
                                <input type="hidden" name="answer_ids[]" value="<?php echo $answer['id']; ?>">
                                
                                <div class="form-group">
                                    <label>Answer Text</label>
                                    <input type="text" name="answer_texts[]" class="form-control" value="<?php echo htmlspecialchars($answer['answer_text']); ?>" required>
                                </div>
                                
                                <div class="form-check">
                                    <input type="<?php echo $question['question_type'] === 'multiple_select' ? 'checkbox' : 'radio'; ?>" 
                                           name="correct[]" 
                                           value="<?php echo $answer['id']; ?>" 
                                           id="correct_<?php echo $answer['id']; ?>"
                                           <?php echo $answer['is_correct'] ? 'checked' : ''; ?>>
                                    <label for="correct_<?php echo $answer['id']; ?>">Correct Answer</label>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Question</button>
                            <a href="questions.php?quiz_id=<?php echo $question['quiz_id']; ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>