<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$attempt_id = intval($_GET['id'] ?? 0);
$user_id = get_user_id();

if (!$attempt_id) {
    set_message('error', 'Invalid attempt');
    redirect('my-courses.php');
}

$sql = "SELECT qa.*, q.title as quiz_title, q.passing_score, q.show_correct_answers, c.id as course_id, c.title as course_title
        FROM quiz_attempts qa
        JOIN quizzes q ON qa.quiz_id = q.id
        JOIN courses c ON q.course_id = c.id
        WHERE qa.id = ? AND qa.user_id = ?";

$result = db_query($conn, $sql, "ii", [$attempt_id, $user_id]);

if (!$result || db_num_rows($result) == 0) {
    set_message('error', 'Quiz attempt not found');
    redirect('my-courses.php');
}

$attempt = db_fetch_assoc($result);
$user_answers = json_decode($attempt['answers_json'], true);

if ($attempt['show_correct_answers']) {
    $questions_sql = "SELECT q.*, 
                      GROUP_CONCAT(a.id, ':', a.answer_text, ':', a.is_correct ORDER BY a.answer_order SEPARATOR '||') as answers
                      FROM questions q
                      LEFT JOIN answers a ON q.id = a.question_id
                      WHERE q.quiz_id = ?
                      GROUP BY q.id
                      ORDER BY q.question_order";
    
    $questions = db_fetch_all(db_query($conn, $questions_sql, "i", [$attempt['quiz_id']]));
    
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
}

$page_title = 'Quiz Results';
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
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 500;
        }
        .nav-item:hover { background: rgba(255, 255, 255, 0.15); }
        .main-content { flex: 1; margin-left: 280px; padding: 40px; }
        .content-header h1 {
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 800;
            color: #4a5568;
            margin-bottom: 40px;
        }
        .result-card {
            background: white;
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border: 2px solid #f7dfd4;
            margin-bottom: 32px;
            text-align: center;
        }
        .result-card h2 {
            font-size: 24px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 32px;
        }
        .score-display {
            width: 200px;
            height: 200px;
            margin: 0 auto 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 56px;
            font-weight: 800;
            color: white;
            position: relative;
        }
        .score-display.passed {
            background: linear-gradient(135deg, #86c3a8 0%, #6fba94 100%);
            box-shadow: 0 12px 32px rgba(134, 195, 168, 0.4);
        }
        .score-display.failed {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            box-shadow: 0 12px 32px rgba(212, 165, 165, 0.4);
        }
        .result-card h3 { font-size: 28px; margin-bottom: 12px; }
        .result-card p { font-size: 16px; color: #718096; margin-bottom: 32px; }
        .result-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin: 32px 0;
            padding: 32px 0;
            border-top: 2px solid #f7dfd4;
            border-bottom: 2px solid #f7dfd4;
        }
        .result-stats div { text-align: center; }
        .result-stats strong {
            display: block;
            font-size: 13px;
            color: #a0aec0;
            margin-bottom: 8px;
        }
        .result-stats span {
            font-size: 18px;
            color: #4a5568;
            font-weight: 700;
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
            margin: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 165, 165, 0.4);
        }
        .btn-secondary {
            background: #f7dfd4;
            color: #9c6b65;
        }
        .btn-secondary:hover { background: #f0d5c9; }
        .review-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
        }
        .review-card h3 {
            font-size: 22px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f7dfd4;
        }
        .question-review {
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid #f7dfd4;
        }
        .question-review:last-child { border-bottom: none; }
        .question-review h4 {
            font-size: 16px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 12px;
        }
        .question-review p {
            font-size: 15px;
            color: #4a5568;
            margin-bottom: 16px;
            line-height: 1.6;
        }
        .answer-option {
            padding: 12px 16px;
            margin: 8px 0;
            border-radius: 12px;
            background: #fef8f5;
            border: 2px solid #f7dfd4;
        }
        .answer-option.correct {
            background: rgba(134, 195, 168, 0.1);
            border-color: #86c3a8;
            color: #4a7c59;
        }
        .answer-option.incorrect {
            background: rgba(212, 165, 165, 0.1);
            border-color: #d4a5a5;
            color: #9c6b65;
        }
        .explanation {
            margin-top: 16px;
            padding: 16px;
            background: #fef8f5;
            border-radius: 12px;
            border-left: 4px solid #d4a5a5;
        }
        .explanation strong { color: #4a5568; }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; padding: 24px; }
            .result-stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>📚 <?php echo htmlspecialchars(truncate($attempt['course_title'], 25)); ?></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="course-detail.php?id=<?php echo $attempt['course_id']; ?>" class="nav-item">← Back to Course</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo htmlspecialchars($attempt['quiz_title']); ?></h1>
            </div>
            
            <?php display_message(); ?>
            
            <div class="result-card">
                <h2>Your Results</h2>
                <div class="score-display <?php echo $attempt['passed'] ? 'passed' : 'failed'; ?>">
                    <?php echo number_format($attempt['score'], 0); ?>%
                </div>
                
                <?php if ($attempt['passed']): ?>
                    <h3 style="color: #4a7c59;">🎉 Congratulations!</h3>
                    <p>You passed the quiz with flying colors!</p>
                <?php else: ?>
                    <h3 style="color: #9c6b65;">Keep Trying!</h3>
                    <p>You need <?php echo $attempt['passing_score']; ?>% to pass. Don't give up!</p>
                <?php endif; ?>
                
                <div class="result-stats">
                    <div>
                        <strong>Score</strong>
                        <span><?php echo $attempt['earned_points']; ?>/<?php echo $attempt['total_points']; ?> points</span>
                    </div>
                    <div>
                        <strong>Time Taken</strong>
                        <span><?php echo gmdate("i:s", $attempt['time_taken']); ?></span>
                    </div>
                    <div>
                        <strong>Date</strong>
                        <span><?php echo format_date($attempt['completed_at']); ?></span>
                    </div>
                </div>
                
                <div>
                    <a href="course-detail.php?id=<?php echo $attempt['course_id']; ?>" class="btn btn-primary">Back to Course</a>
                    <a href="quiz.php?id=<?php echo $attempt['quiz_id']; ?>" class="btn btn-secondary">Retake Quiz</a>
                </div>
            </div>
            
            <?php if ($attempt['show_correct_answers'] && isset($questions)): ?>
            <div class="review-card">
                <h3>📝 Review Answers</h3>
                <?php foreach ($questions as $index => $question): ?>
                <div class="question-review">
                    <h4>Question <?php echo $index + 1; ?></h4>
                    <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                    
                    <?php
                    $user_answer_key = 'question_' . $question['id'];
                    $user_answer_value = $user_answers[$user_answer_key] ?? null;
                    
                    foreach ($question['answers_array'] as $answer):
                        $is_user_answer = false;
                        if (is_array($user_answer_value)) {
                            $is_user_answer = in_array($answer['id'], $user_answer_value);
                        } else {
                            $is_user_answer = ($answer['id'] == $user_answer_value);
                        }
                        
                        $class = '';
                        if ($answer['is_correct']) {
                            $class = 'answer-option correct';
                        } elseif ($is_user_answer) {
                            $class = 'answer-option incorrect';
                        } else {
                            $class = 'answer-option';
                        }
                    ?>
                    <div class="<?php echo $class; ?>">
                        <?php if ($is_user_answer): ?>
                            <strong>Your answer: </strong>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($answer['answer_text']); ?>
                        <?php if ($answer['is_correct']): ?>
                            <span style="color: #4a7c59; font-weight: bold;"> ✓ Correct</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if ($question['explanation']): ?>
                    <div class="explanation">
                        <strong>💡 Explanation:</strong> <?php echo htmlspecialchars($question['explanation']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>