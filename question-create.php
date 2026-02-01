<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();
$quiz_id = intval($_GET['quiz_id'] ?? 0);
if (!$quiz_id)
    redirect('courses.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $question_text = sanitize($_POST['question_text'] ?? '');
    $question_type = sanitize($_POST['question_type'] ?? 'mcq');
    $points = intval($_POST['points'] ?? 1);
    $question_order = intval($_POST['question_order'] ?? 1);

    $sql = "INSERT INTO questions (quiz_id, question_text, question_type, points, question_order) VALUES (?, ?, ?, ?, ?)";
    $result = db_query($conn, $sql, "issii", [$quiz_id, $question_text, $question_type, $points, $question_order]);

    if ($result) {
        $question_id = db_insert_id($conn);
        $answers = $_POST['answers'] ?? [];
        $correct = $_POST['correct'] ?? [];

        foreach ($answers as $index => $answer_text) {
            if (!empty($answer_text)) {
                $is_correct = in_array($index, $correct) ? 1 : 0;
                $ans_sql = "INSERT INTO answers (question_id, answer_text, is_correct, answer_order) VALUES (?, ?, ?, ?)";
                db_query($conn, $ans_sql, "isii", [$question_id, sanitize($answer_text), $is_correct, $index]);
            }
        }

        set_message('success', 'Question created');
        redirect('questions.php?quiz_id=' . $quiz_id);
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Create Question</title>
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
            <h1>Create Question</h1><?php display_message(); ?>
            <div class="card">
                <div class="card-body">
                    <form method="POST" id="questionForm"><?php echo csrf_field(); ?>
                        <div class="form-group"><label>Question Text *</label><textarea name="question_text"
                                class="form-control" rows="3" required></textarea></div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group"><label>Question Type</label><select name="question_type"
                                        class="form-control" id="questionType">
                                        <option value="mcq">Multiple Choice</option>
                                        <option value="true_false">True/False</option>
                                        <option value="multiple_select">Multiple Select</option>
                                    </select></div>
                            </div>
                            <div class="col-3">
                                <div class="form-group"><label>Points</label><input type="number" name="points"
                                        class="form-control" value="1" min="1"></div>
                            </div>
                            <div class="col-3">
                                <div class="form-group"><label>Order</label><input type="number" name="question_order"
                                        class="form-control" value="1" min="1"></div>
                            </div>
                        </div>
                        <div id="answersContainer">
                            <h4>Answers</h4>
                            <div class="answer-item mb-2"><input type="text" name="answers[]" class="form-control"
                                    placeholder="Answer text" required><input type="checkbox" name="correct[]"
                                    value="0"> Correct</div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm mb-3" onclick="addAnswer()">+ Add
                            Answer</button>
                        <div><button type="submit" class="btn btn-primary">Create Question</button><a
                                href="questions.php?quiz_id=<?php echo $quiz_id; ?>"
                                class="btn btn-secondary">Cancel</a></div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>let answerIndex = 1; function addAnswer() { const div = document.createElement('div'); div.className = 'answer-item mb-2'; div.innerHTML = '<input type="text" name="answers[]" class="form-control" placeholder="Answer text"><input type="checkbox" name="correct[]" value="' + answerIndex + '"> Correct'; document.getElementById('answersContainer').appendChild(div); answerIndex++; }</script>
</body>

</html>