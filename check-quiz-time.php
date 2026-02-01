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

$quiz_id = intval($_GET['quiz_id'] ?? 0);
$started_at = intval($_GET['started_at'] ?? 0);

if (!$quiz_id || !$started_at) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$sql = "SELECT time_limit FROM quizzes WHERE id = ?";
$result = db_query($conn, $sql, "i", [$quiz_id]);
$quiz = db_fetch_assoc($result);

if (!$quiz) {
    echo json_encode(['success' => false, 'message' => 'Quiz not found']);
    exit;
}

$elapsed = time() - $started_at;
$time_limit_seconds = $quiz['time_limit'] * 60;
$remaining = max(0, $time_limit_seconds - $elapsed);

echo json_encode([
    'success' => true,
    'remaining' => $remaining,
    'expired' => $remaining <= 0
]);
?>