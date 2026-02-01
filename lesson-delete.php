<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();
$lesson_id = intval($_GET['id'] ?? 0);
$course_id = intval($_GET['course_id'] ?? 0);

if (!$lesson_id) {
    set_message('error', 'Invalid lesson');
    redirect('courses.php');
}

$sql = "SELECT l.*, c.created_by FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.id = ?";
$result = db_query($conn, $sql, "i", [$lesson_id]);
$lesson = db_fetch_assoc($result);

if (!$lesson || !owns_resource($conn, 'courses', $lesson['course_id'], 'created_by', $user_id)) {
    set_message('error', 'Access denied');
    redirect('courses.php');
}

if ($lesson['file_attachment']) {
    delete_file(UPLOAD_PATH . '/lessons/' . $lesson['file_attachment']);
}

$sql = "DELETE FROM lessons WHERE id = ?";
$result = db_query($conn, $sql, "i", [$lesson_id]);

if ($result) {
    set_message('success', 'Lesson deleted successfully');
} else {
    set_message('error', 'Failed to delete lesson');
}

redirect('lessons.php?course_id=' . $lesson['course_id']);
?>