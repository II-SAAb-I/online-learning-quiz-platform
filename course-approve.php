<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_admin();

$course_id = intval($_GET['id'] ?? 0);
$action = sanitize($_GET['action'] ?? '');

if (!$course_id || !in_array($action, ['publish', 'unpublish'])) {
    set_message('error', 'Invalid request');
    redirect('courses.php');
}

$sql = "SELECT id FROM courses WHERE id = ?";
$result = db_query($conn, $sql, "i", [$course_id]);

if (!$result || db_num_rows($result) == 0) {
    set_message('error', 'Course not found');
    redirect('courses.php');
}

$is_published = ($action === 'publish') ? 1 : 0;

$sql = "UPDATE courses SET is_published = ? WHERE id = ?";
$result = db_query($conn, $sql, "ii", [$is_published, $course_id]);

if ($result) {
    $message = ($action === 'publish') ? 'Course published successfully' : 'Course unpublished successfully';
    set_message('success', $message);
} else {
    set_message('error', 'Failed to update course status');
}

redirect('courses.php');
?>