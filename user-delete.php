<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_admin();

$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    set_message('error', 'Invalid user ID');
    redirect('users.php');
}

if ($user_id == get_user_id()) {
    set_message('error', 'You cannot delete your own account');
    redirect('users.php');
}

$sql = "SELECT id FROM users WHERE id = ?";
$result = db_query($conn, $sql, "i", [$user_id]);

if (!$result || db_num_rows($result) == 0) {
    set_message('error', 'User not found');
    redirect('users.php');
}

$sql = "DELETE FROM users WHERE id = ?";
$result = db_query($conn, $sql, "i", [$user_id]);

if ($result) {
    set_message('success', 'User deleted successfully');
} else {
    set_message('error', 'Failed to delete user');
}

redirect('users.php');
?>