<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_admin();

$search = sanitize($_GET['search'] ?? '');
$role_filter = sanitize($_GET['role'] ?? '');

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (full_name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

if (!empty($params)) {
    $result = db_query($conn, $sql, $types, $params);
} else {
    $result = db_query($conn, $sql);
}

$users = db_fetch_all($result);

$page_title = 'Manage Users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/dashboard.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🎓 Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    📊 Dashboard
                </a>
                <a href="users.php" class="nav-item active">
                    👥 Users
                </a>
                <a href="courses.php" class="nav-item">
                    📚 Courses
                </a>
                <a href="analytics.php" class="nav-item">
                    📈 Analytics
                </a>
                <a href="reports.php" class="nav-item">
                    📄 Reports
                </a>
                <a href="../logout.php" class="nav-item">
                    🚪 Logout
                </a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="user-create.php" class="btn btn-primary">+ Add New User</a>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="mb-4">
                        <div class="row">
                            <div class="col-6">
                                <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-3">
                                <select name="role" class="form-control">
                                    <option value="">All Roles</option>
                                    <option value="student" <?php echo $role_filter === 'student' ? 'selected' : ''; ?>>Student</option>
                                    <option value="instructor" <?php echo $role_filter === 'instructor' ? 'selected' : ''; ?>>Instructor</option>
                                    <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <a href="users.php" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($user['created_at']); ?></td>
                                <td>
                                    <a href="user-edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <?php if ($user['id'] != get_user_id()): ?>
                                    <a href="user-delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No users found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>