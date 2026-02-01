<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();
$quiz_id = intval($_GET['id'] ?? 0);
$course_id = intval($_GET['course_id'] ?? 0);

if (!$quiz_id) {
    set_message('error', 'Invalid quiz');
    redirect('courses.php');
}

$sql = "SELECT q.*, c.created_by FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ?";
$result = db_query($conn, $sql, "i", [$quiz_id]);
$quiz = db_fetch_assoc($result);

if (!$quiz || !owns_resource($conn, 'courses', $quiz['course_id'], 'created_by', $user_id)) {
    set_message('error', 'Access denied');
    redirect('courses.php');
}

$sql = "DELETE FROM quizzes WHERE id = ?";
$result = db_query($conn, $sql, "i", [$quiz_id]);

if ($result) {
    set_message('success', 'Quiz deleted successfully');
} else {
    set_message('error', 'Failed to delete quiz');
}

redirect('quizzes.php?course_id=' . $quiz['course_id']);
?>