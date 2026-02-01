<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$lesson_id = intval($_GET['id'] ?? 0);
$user_id = get_user_id();

if (!$lesson_id) {
    set_message('error', 'Invalid lesson');
    redirect('my-courses.php');
}

$lesson_sql = "SELECT l.*, c.id as course_id FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.id = ?";
$lesson_result = db_query($conn, $lesson_sql, "i", [$lesson_id]);

if (!$lesson_result || db_num_rows($lesson_result) == 0) {
    set_message('error', 'Lesson not found');
    redirect('my-courses.php');
}

$lesson = db_fetch_assoc($lesson_result);

if (!can_access_course($conn, $lesson['course_id'], $user_id)) {
    set_message('error', 'Access denied');
    redirect('my-courses.php');
}

$check_sql = "SELECT id FROM lesson_completion WHERE lesson_id = ? AND user_id = ?";
$check_result = db_query($conn, $check_sql, "ii", [$lesson_id, $user_id]);

if (db_num_rows($check_result) == 0) {
    $sql = "INSERT INTO lesson_completion (lesson_id, user_id) VALUES (?, ?)";
    db_query($conn, $sql, "ii", [$lesson_id, $user_id]);
    
    $total_lessons_sql = "SELECT COUNT(*) as total FROM lessons WHERE course_id = ?";
    $total_result = db_query($conn, $total_lessons_sql, "i", [$lesson['course_id']]);
    $total = db_fetch_assoc($total_result)['total'];
    
    $completed_sql = "SELECT COUNT(DISTINCT lc.lesson_id) as completed
                      FROM lesson_completion lc
                      JOIN lessons l ON lc.lesson_id = l.id
                      WHERE l.course_id = ? AND lc.user_id = ?";
    $completed_result = db_query($conn, $completed_sql, "ii", [$lesson['course_id'], $user_id]);
    $completed = db_fetch_assoc($completed_result)['completed'];
    
    $progress = ($total > 0) ? ($completed / $total) * 100 : 0;
    $is_completed = ($progress >= 100) ? 1 : 0;
    
    $update_sql = "UPDATE enrollments SET progress_percentage = ?, is_completed = ?, completed_at = ? WHERE course_id = ? AND user_id = ?";
    $completed_at = $is_completed ? date('Y-m-d H:i:s') : null;
    db_query($conn, $update_sql, "disii", [$progress, $is_completed, $completed_at, $lesson['course_id'], $user_id]);
    
    set_message('success', 'Lesson marked as complete!');
} else {
    set_message('info', 'Lesson already completed');
}

redirect('lesson.php?id=' . $lesson_id);
?>