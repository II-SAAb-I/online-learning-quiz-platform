<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token');
    } else {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = sanitize($_POST['role'] ?? ROLE_STUDENT);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $errors = [];
        
        if (empty($full_name)) {
            $errors[] = 'Full name is required';
        }
        
        if (empty($email) || !validate_email($email)) {
            $errors[] = 'Valid email is required';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        if (email_exists($conn, $email)) {
            $errors[] = 'Email already exists';
        }
        
        if (empty($errors)) {
            $hashed_password = hash_password($password);
            
            $sql = "INSERT INTO users (full_name, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)";
            $result = db_query($conn, $sql, "ssssi", [$full_name, $email, $hashed_password, $role, $is_active]);
            
            if ($result) {
                set_message('success', 'User created successfully');
                redirect('users.php');
            } else {
                set_message('error', 'Failed to create user');
            }
        } else {
            set_message('error', implode('<br>', $errors));
        }
    }
}

$page_title = 'Create User';
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
                <a href="users.php" class="btn btn-secondary">← Back to Users</a>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                            <small class="form-text">Minimum <?php echo MIN_PASSWORD_LENGTH; ?> characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="student">Student</option>
                                <option value="instructor">Instructor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            <label for="is_active">Active Account</label>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Create User</button>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>