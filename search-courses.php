<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

$search = sanitize($_GET['q'] ?? '');
$category = sanitize($_GET['category'] ?? '');

$sql = "SELECT c.id, c.title, c.short_description, c.category, c.difficulty, u.full_name as instructor_name
        FROM courses c
        JOIN users u ON c.created_by = u.id
        WHERE c.is_published = 1";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (c.title LIKE ? OR c.short_description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($category)) {
    $sql .= " AND c.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " LIMIT 10";

if (!empty($params)) {
    $result = db_query($conn, $sql, $types, $params);
} else {
    $result = db_query($conn, $sql);
}

$courses = db_fetch_all($result);

echo json_encode(['success' => true, 'courses' => $courses]);
?>