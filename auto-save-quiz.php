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

$quiz_id = intval($_POST['quiz_id'] ?? 0);
$answers = $_POST['answers'] ?? [];
$user_id = get_user_id();

if (!$quiz_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz']);
    exit;
}

$_SESSION['quiz_' . $quiz_id . '_answers'] = $answers;

echo json_encode(['success' => true, 'message' => 'Progress saved']);
?>