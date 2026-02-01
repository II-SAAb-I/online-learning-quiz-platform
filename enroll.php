<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$course_id = intval($_GET['id'] ?? 0);
$user_id = get_user_id();

if (!$course_id) {
    set_message('error', 'Invalid course');
    redirect('courses.php');
}

$check_sql = "SELECT id FROM courses WHERE id = ? AND is_published = 1";
$check_result = db_query($conn, $check_sql, "i", [$course_id]);

if (!$check_result || db_num_rows($check_result) == 0) {
    set_message('error', 'Course not found');
    redirect('courses.php');
}

$enrolled_sql = "SELECT id FROM enrollments WHERE course_id = ? AND user_id = ?";
$enrolled_result = db_query($conn, $enrolled_sql, "ii", [$course_id, $user_id]);

if (db_num_rows($enrolled_result) > 0) {
    set_message('info', 'You are already enrolled in this course');
    redirect('course-detail.php?id=' . $course_id);
}

$sql = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";
$result = db_query($conn, $sql, "ii", [$user_id, $course_id]);

if ($result) {
    $update_sql = "UPDATE courses SET enrollment_count = enrollment_count + 1 WHERE id = ?";
    db_query($conn, $update_sql, "i", [$course_id]);
    
    set_message('success', 'Successfully enrolled in course!');
    redirect('course-detail.php?id=' . $course_id);
} else {
    set_message('error', 'Failed to enroll. Please try again.');
    redirect('course-detail.php?id=' . $course_id);
}
?>