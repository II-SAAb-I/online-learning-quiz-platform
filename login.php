<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';

redirect_if_logged_in();

$timeout_message = $_SESSION['timeout_message'] ?? null;
unset($_SESSION['timeout_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token. Please try again.');
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);
        
        if (empty($email) || empty($password)) {
            set_message('error', 'Please enter both email and password.');
        } else {
            $result = login_user($conn, $email, $password, $remember_me);
            
            if ($result['success']) {
                set_message('success', 'Welcome back!');
                
                $redirect = $_SESSION['redirect_after_login'] ?? get_dashboard_url();
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
            } else {
                set_message('error', $result['message']);
            }
        }
    }
}

$page_title = 'Login';
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
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            padding: 48px 40px;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 36px;
        }

        .auth-logo .logo-icon {
            font-size: 56px;
            margin-bottom: 16px;
            display: inline-block;
            animation: bounce 0.6s ease-out;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .auth-logo h1 {
            color: #4a5568;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .auth-logo p {
            color: #718096;
            font-size: 16px;
            font-weight: 400;
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

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }

        .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
            accent-color: #d4a5a5;
        }

        .form-check label {
            color: #718096;
            font-size: 14px;
            cursor: pointer;
            user-select: none;
        }

        .btn {
            width: 100%;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(212, 165, 165, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 165, 165, 0.5);
        }

        .divider {
            position: relative;
            text-align: center;
            margin: 32px 0;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #f7dfd4;
        }

        .divider span {
            position: relative;
            background: white;
            padding: 0 16px;
            color: #a0aec0;
            font-size: 13px;
            font-weight: 500;
        }

        .text-center {
            text-align: center;
        }

        .text-center p {
            color: #718096;
            font-size: 15px;
            margin: 0;
        }

        .text-center a {
            color: #d4a5a5;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .text-center a:hover {
            color: #c79a94;
        }

        .demo-credentials {
            background: linear-gradient(135deg, #fef8f5 0%, #fef5f1 100%);
            border: 2px solid #f7dfd4;
            border-radius: 16px;
            padding: 20px;
            margin-top: 28px;
        }

        .demo-title {
            color: #4a5568;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .demo-title::before,
        .demo-title::after {
            content: '';
            height: 1px;
            width: 30px;
            background: #d4a5a5;
        }

        .demo-credentials p {
            color: #718096;
            font-size: 13px;
            line-height: 1.8;
            margin: 8px 0;
            padding: 8px 12px;
            background: white;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            border: 1px solid #f7dfd4;
        }

        .demo-credentials strong {
            color: #4a5568;
            font-weight: 700;
            display: inline-block;
            min-width: 90px;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-warning {
            background: rgba(247, 223, 212, 0.4);
            border: 1px solid #f7dfd4;
            color: #9c6b65;
        }

        .alert-error {
            background: rgba(212, 165, 165, 0.15);
            border: 1px solid #d4a5a5;
            color: #9c6b65;
        }

        .alert-success {
            background: rgba(134, 195, 168, 0.15);
            border: 1px solid #86c3a8;
            color: #4a7c59;
        }

        .mt-3 {
            margin-top: 24px;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 36px 28px;
            }

            .auth-logo h1 {
                font-size: 28px;
            }

            .auth-logo .logo-icon {
                font-size: 48px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="logo-icon">🎓</div>
                <h1>LearnHub</h1>
                <p>Welcome back! Please login to continue.</p>
            </div>
            
            <?php if ($timeout_message): ?>
                <div class="alert alert-warning">
                    <?php echo htmlspecialchars($timeout_message); ?>
                </div>
            <?php endif; ?>
            
            <?php display_message(); ?>
            
            <form method="POST" action="" id="loginForm">
                <?php echo csrf_field(); ?>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="your.email@example.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required 
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter your password"
                        required
                    >
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="remember_me" name="remember_me">
                    <label for="remember_me">Remember me</label>
                </div>
                
                <button type="submit" class="btn btn-primary mt-3">
                    Login
                </button>
            </form>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <div class="text-center">
                <p>
                    Don't have an account? 
                    <a href="register.php">Sign up now</a>
                </p>
            </div>
            
            <div class="demo-credentials">
                <p class="demo-title">Demo Credentials</p>
                <div>
                    <p><strong>Student:</strong> john.student@learning.com / password123</p>
                    <p><strong>Instructor:</strong> sarah.instructor@learning.com / password123</p>
                    <p><strong>Admin:</strong> admin@learning.com / password123</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo ASSETS_URL; ?>/js/validation.js"></script>
</body>
</html>