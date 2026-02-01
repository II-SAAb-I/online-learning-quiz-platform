<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_instructor();

$user_id = get_user_id();
$course_id = intval($_GET['id'] ?? 0);

if (!$course_id) {
    set_message('error', 'Invalid course');
    redirect('courses.php');
}

if (!owns_resource($conn, 'courses', $course_id, 'created_by', $user_id)) {
    set_message('error', 'Access denied');
    redirect('courses.php');
}

$sql = "SELECT thumbnail FROM courses WHERE id = ?";
$result = db_query($conn, $sql, "i", [$course_id]);
$course = db_fetch_assoc($result);

$sql = "DELETE FROM courses WHERE id = ?";
$result = db_query($conn, $sql, "i", [$course_id]);

if ($result) {
    if ($course['thumbnail']) {
        delete_file(UPLOAD_PATH . '/courses/' . $course['thumbnail']);
    }
    set_message('success', 'Course deleted successfully');
} else {
    set_message('error', 'Failed to delete course');
}

redirect('courses.php');
?>