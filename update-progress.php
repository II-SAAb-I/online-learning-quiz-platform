<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$course_id = intval($_POST['course_id'] ?? 0);
$user_id = get_user_id();

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course']);
    exit;
}

$total_sql = "SELECT COUNT(*) as total FROM lessons WHERE course_id = ?";
$total_result = db_query($conn, $total_sql, "i", [$course_id]);
$total = db_fetch_assoc($total_result)['total'];

$completed_sql = "SELECT COUNT(DISTINCT lc.lesson_id) as completed
                  FROM lesson_completion lc
                  JOIN lessons l ON lc.lesson_id = l.id
                  WHERE l.course_id = ? AND lc.user_id = ?";
$completed_result = db_query($conn, $completed_sql, "ii", [$course_id, $user_id]);
$completed = db_fetch_assoc($completed_result)['completed'];

$progress = ($total > 0) ? ($completed / $total) * 100 : 0;
$is_completed = ($progress >= 100) ? 1 : 0;

$update_sql = "UPDATE enrollments SET progress_percentage = ?, is_completed = ? WHERE course_id = ? AND user_id = ?";
db_query($conn, $update_sql, "diii", [$progress, $is_completed, $course_id, $user_id]);

echo json_encode([
    'success' => true,
    'progress' => round($progress, 2),
    'completed' => $is_completed
]);
?>