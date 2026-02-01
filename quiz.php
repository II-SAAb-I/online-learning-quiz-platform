<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$quiz_id = intval($_GET['id'] ?? 0);
$user_id = get_user_id();

if (!$quiz_id) {
    set_message('error', 'Invalid quiz');
    redirect('my-courses.php');
}

$sql = "SELECT q.*, c.id as course_id, c.title as course_title
        FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        WHERE q.id = ?";

$result = db_query($conn, $sql, "i", [$quiz_id]);

if (!$result || db_num_rows($result) == 0) {
    set_message('error', 'Quiz not found');
    redirect('my-courses.php');
}

$quiz = db_fetch_assoc($result);

if (!can_access_course($conn, $quiz['course_id'], $user_id)) {
    set_message('error', 'You must enroll in this course first');
    redirect('course-detail.php?id=' . $quiz['course_id']);
}

$attempts_sql = "SELECT COUNT(*) as attempt_count FROM quiz_attempts WHERE quiz_id = ? AND user_id = ?";
$attempts_result = db_query($conn, $attempts_sql, "ii", [$quiz_id, $user_id]);
$attempts = db_fetch_assoc($attempts_result);

if ($quiz['max_attempts'] && $attempts['attempt_count'] >= $quiz['max_attempts']) {
    set_message('error', 'Maximum attempts reached for this quiz');
    redirect('course-detail.php?id=' . $quiz['course_id']);
}

$questions_sql = "SELECT q.*, GROUP_CONCAT(a.id, ':', a.answer_text, ':', a.is_correct ORDER BY a.answer_order SEPARATOR '||') as answers
                  FROM questions q
                  LEFT JOIN answers a ON q.id = a.question_id
                  WHERE q.quiz_id = ?
                  GROUP BY q.id
                  ORDER BY q.question_order";

$questions = db_fetch_all(db_query($conn, $questions_sql, "i", [$quiz_id]));

foreach ($questions as &$question) {
    $answers_array = [];
    if ($question['answers']) {
        $answers_parts = explode('||', $question['answers']);
        foreach ($answers_parts as $answer) {
            list($id, $text, $is_correct) = explode(':', $answer);
            $answers_array[] = [
                'id' => $id,
                'answer_text' => $text,
                'is_correct' => $is_correct
            ];
        }
    }
    $question['answers_array'] = $answers_array;
}

$page_title = $quiz['title'];
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
        .sidebar-header { padding: 32px 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.2); }
        .sidebar-header h2 { font-size: 18px; font-weight: 700; color: white; }
        .sidebar-nav { padding: 24px 16px; }
        .nav-item {
            display: block;
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
        .main-content { flex: 1; margin-left: 280px; padding: 40px; }
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
        .timer {
            padding: 16px 32px;
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
            border-radius: 16px;
            font-size: 24px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(212, 165, 165, 0.4);
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
            margin-bottom: 24px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
        }
        .info-item strong {
            display: block;
            font-size: 13px;
            color: #a0aec0;
            margin-bottom: 4px;
        }
        .info-item span {
            font-size: 16px;
            color: #4a5568;
            font-weight: 600;
        }
        .question-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
            margin-bottom: 24px;
        }
        .question-card h4 {
            font-size: 18px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f7dfd4;
        }
        .question-card p {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .form-check {
            display: flex;
            align-items: flex-start;
            padding: 16px;
            margin-bottom: 12px;
            background: #fef8f5;
            border: 2px solid #f7dfd4;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .form-check:hover {
            background: white;
            border-color: #d4a5a5;
            transform: translateX(4px);
        }
        .form-check input {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            margin-top: 2px;
            cursor: pointer;
            accent-color: #d4a5a5;
            flex-shrink: 0;
        }
        .form-check label {
            cursor: pointer;
            font-size: 15px;
            color: #4a5568;
            line-height: 1.5;
            flex: 1;
        }
        .btn {
            padding: 16px 48px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 165, 165, 0.4);
        }
        .text-center { text-align: center; }
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
                <h2>📚 <?php echo htmlspecialchars(truncate($quiz['course_title'], 25)); ?></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="course-detail.php?id=<?php echo $quiz['course_id']; ?>" class="nav-item">← Back to Course</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
                <?php if ($quiz['time_limit']): ?>
                <div class="timer">
                    ⏱️ <span id="time-remaining"><?php echo $quiz['time_limit']; ?>:00</span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Questions</strong>
                        <span><?php echo count($questions); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Passing Score</strong>
                        <span><?php echo $quiz['passing_score']; ?>%</span>
                    </div>
                    <?php if ($quiz['time_limit']): ?>
                    <div class="info-item">
                        <strong>Time Limit</strong>
                        <span><?php echo $quiz['time_limit']; ?> minutes</span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <strong>Attempts</strong>
                        <span><?php echo $attempts['attempt_count']; ?><?php echo $quiz['max_attempts'] ? '/' . $quiz['max_attempts'] : ''; ?></span>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="quiz-submit.php" id="quizForm">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                <input type="hidden" name="started_at" value="<?php echo time(); ?>">
                <?php echo csrf_field(); ?>
                
                <?php foreach ($questions as $index => $question): ?>
                <div class="question-card">
                    <h4>Question <?php echo $index + 1; ?></h4>
                    <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                    
                    <?php if ($question['question_type'] === 'mcq' || $question['question_type'] === 'true_false'): ?>
                        <?php foreach ($question['answers_array'] as $answer): ?>
                        <div class="form-check">
                            <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo $answer['id']; ?>" id="answer_<?php echo $answer['id']; ?>" required>
                            <label for="answer_<?php echo $answer['id']; ?>"><?php echo htmlspecialchars($answer['answer_text']); ?></label>
                        </div>
                        <?php endforeach; ?>
                    <?php elseif ($question['question_type'] === 'multiple_select'): ?>
                        <?php foreach ($question['answers_array'] as $answer): ?>
                        <div class="form-check">
                            <input type="checkbox" name="question_<?php echo $question['id']; ?>[]" value="<?php echo $answer['id']; ?>" id="answer_<?php echo $answer['id']; ?>">
                            <label for="answer_<?php echo $answer['id']; ?>"><?php echo htmlspecialchars($answer['answer_text']); ?></label>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">📝 Submit Quiz</button>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        <?php if ($quiz['time_limit']): ?>
        let timeLimit = <?php echo $quiz['time_limit'] * 60; ?>;
        let timeRemaining = timeLimit;
        
        function updateTimer() {
            let minutes = Math.floor(timeRemaining / 60);
            let seconds = timeRemaining % 60;
            document.getElementById('time-remaining').textContent = 
                minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
            
            if (timeRemaining <= 0) {
                alert('Time is up! The quiz will be submitted automatically.');
                document.getElementById('quizForm').submit();
            }
            
            timeRemaining--;
        }
        
        updateTimer();
        setInterval(updateTimer, 1000);
        <?php endif; ?>
        
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to submit your answers?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>