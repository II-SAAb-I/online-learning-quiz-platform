<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$user_id = get_user_id();

$sql = "SELECT * FROM users WHERE id = ?";
$result = db_query($conn, $sql, "i", [$user_id]);
$user = db_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token');
    } else {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($full_name) || empty($email)) {
            set_message('error', 'Name and email are required');
        } elseif (email_exists($conn, $email, $user_id)) {
            set_message('error', 'Email already exists');
        } else {
            if (!empty($password)) {
                $hashed_password = hash_password($password);
                $update_sql = "UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?";
                db_query($conn, $update_sql, "sssi", [$full_name, $email, $hashed_password, $user_id]);
            } else {
                $update_sql = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
                db_query($conn, $update_sql, "ssi", [$full_name, $email, $user_id]);
            }
            
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            
            set_message('success', 'Profile updated successfully');
            redirect('profile.php');
        }
    }
}

$page_title = 'My Profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background: linear-gradient(135deg, #fef5f1 0%, #fef8f5 100%);
            color: #2d3748;
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #c9ada7 0%, #a89f9c 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 32px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-header h2 {
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
        }

        .sidebar-nav {
            padding: 24px 16px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(4px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(212, 165, 165, 0.4);
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
        }

        .content-header h1 {
            font-size: clamp(28px, 3vw, 36px);
            font-weight: 800;
            color: #4a5568;
            letter-spacing: -0.5px;
            margin-bottom: 40px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
        }

        .card {
            background: white;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 2px solid #f7dfd4;
        }

        .card h3 {
            font-size: 22px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f7dfd4;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            color: #4a5568;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            font-size: 15px;
            border: 2px solid #f7dfd4;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #fef8f5;
        }

        .form-control:focus {
            outline: none;
            border-color: #d4a5a5;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(212, 165, 165, 0.1);
        }

        .form-text {
            display: block;
            margin-top: 6px;
            color: #a0aec0;
            font-size: 13px;
        }

        .btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 165, 165, 0.4);
        }

        .info-item {
            padding: 20px;
            background: #fef8f5;
            border-radius: 12px;
            margin-bottom: 16px;
            border: 2px solid #f7dfd4;
        }

        .info-label {
            font-size: 12px;
            font-weight: 700;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 16px;
            color: #4a5568;
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success {
            background: linear-gradient(135deg, rgba(134, 195, 168, 0.2) 0%, rgba(134, 195, 168, 0.3) 100%);
            color: #4a7c59;
        }

        .badge-danger {
            background: linear-gradient(135deg, rgba(212, 165, 165, 0.2) 0%, rgba(199, 154, 148, 0.3) 100%);
            color: #9c6b65;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .alert-success {
            background: rgba(134, 195, 168, 0.15);
            border: 1px solid #86c3a8;
            color: #4a7c59;
        }

        .alert-error {
            background: rgba(212, 165, 165, 0.15);
            border: 1px solid #d4a5a5;
            color: #9c6b65;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
                padding: 24px;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🎓 Student Portal</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">📊 Dashboard</a>
                <a href="courses.php" class="nav-item">📚 Browse Courses</a>
                <a href="my-courses.php" class="nav-item">📖 My Courses</a>
                <a href="my-certificates.php" class="nav-item">🏆 Certificates</a>
                <a href="profile.php" class="nav-item active">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1>👤 My Profile</h1>
            </div>
            
            <?php display_message(); ?>
            
            <div class="profile-grid">
                <div class="card">
                    <h3>Edit Profile</h3>
                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                            <small class="form-text">Leave blank to keep current password</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">💾 Update Profile</button>
                    </form>
                </div>
                
                <div>
                    <div class="card">
                        <h3>Account Info</h3>
                        
                        <div class="info-item">
                            <div class="info-label">Role</div>
                            <div class="info-value"><?php echo ucfirst($user['role']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Member Since</div>
                            <div class="info-value"><?php echo format_date($user['created_at']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Account Status</div>
                            <div class="info-value">
                                <?php if ($user['is_active']): ?>
                                    <span class="badge badge-success">✓ Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">✗ Inactive</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>