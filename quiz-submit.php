<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('my-courses.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_message('error', 'Invalid security token');
    redirect('my-courses.php');
}

$quiz_id = intval($_POST['quiz_id'] ?? 0);
$user_id = get_user_id();
$started_at = intval($_POST['started_at'] ?? 0);

if (!$quiz_id) {
    set_message('error', 'Invalid quiz');
    redirect('my-courses.php');
}

$quiz_sql = "SELECT q.*, c.id as course_id FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ?";
$quiz_result = db_query($conn, $quiz_sql, "i", [$quiz_id]);

if (!$quiz_result || db_num_rows($quiz_result) == 0) {
    set_message('error', 'Quiz not found');
    redirect('my-courses.php');
}

$quiz = db_fetch_assoc($quiz_result);

$questions_sql = "SELECT id, points, question_type FROM questions WHERE quiz_id = ?";
$questions = db_fetch_all(db_query($conn, $questions_sql, "i", [$quiz_id]));

$total_points = 0;
$earned_points = 0;
$answers_data = [];

foreach ($questions as $question) {
    $total_points += $question['points'];
    
    $user_answer = $_POST['question_' . $question['id']] ?? null;
    
    if ($question['question_type'] === 'multiple_select') {
        $user_answer = is_array($user_answer) ? $user_answer : [];
        
        $correct_answers_sql = "SELECT id FROM answers WHERE question_id = ? AND is_correct = 1";
        $correct_answers = db_fetch_all(db_query($conn, $correct_answers_sql, "i", [$question['id']]));
        $correct_ids = array_column($correct_answers, 'id');
        
        sort($user_answer);
        sort($correct_ids);
        
        if ($user_answer == $correct_ids) {
            $earned_points += $question['points'];
        }
        
        $answers_data['question_' . $question['id']] = $user_answer;
    } else {
        if ($user_answer) {
            $check_sql = "SELECT is_correct FROM answers WHERE id = ?";
            $check_result = db_query($conn, $check_sql, "i", [$user_answer]);
            $answer = db_fetch_assoc($check_result);
            
            if ($answer && $answer['is_correct']) {
                $earned_points += $question['points'];
            }
        }
        
        $answers_data['question_' . $question['id']] = $user_answer;
    }
}

$score = ($total_points > 0) ? ($earned_points / $total_points) * 100 : 0;
$passed = ($score >= $quiz['passing_score']) ? 1 : 0;
$time_taken = time() - $started_at;

$insert_sql = "INSERT INTO quiz_attempts (quiz_id, user_id, score, total_points, earned_points, passed, completed_at, time_taken, answers_json) 
               VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)";

$answers_json = json_encode($answers_data);

db_query($conn, $insert_sql, "iidiiiis", [
    $quiz_id,
    $user_id,
    $score,
    $total_points,
    $earned_points,
    $passed,
    $time_taken,
    $answers_json
]);

$attempt_id = db_insert_id($conn);

set_message('success', 'Quiz submitted successfully!');
redirect('quiz-result.php?id=' . $attempt_id);
?>