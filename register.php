<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';

redirect_if_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token. Please try again.');
    } else {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = sanitize($_POST['role'] ?? ROLE_STUDENT);
        
        $errors = [];
        
        if (empty($full_name)) {
            $errors[] = 'Full name is required';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!validate_email($email)) {
            $errors[] = 'Invalid email address';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!in_array($role, [ROLE_STUDENT, ROLE_INSTRUCTOR])) {
            $role = ROLE_STUDENT;
        }
        
        if (empty($errors)) {
            $result = register_user($conn, [
                'full_name' => $full_name,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ]);
            
            if ($result['success']) {
                set_message('success', 'Registration successful! Please login.');
                redirect('login.php');
            } else {
                set_message('error', $result['message']);
            }
        } else {
            set_message('error', implode('<br>', $errors));
        }
    }
}

$page_title = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            max-width: 520px;
            width: 100%;
            padding: 48px 40px;
            animation: slideUp 0.4s ease-out;
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        .auth-logo { text-align: center; margin-bottom: 36px; }
        .logo-icon { font-size: 56px; margin-bottom: 16px; display: inline-block; }
        h1 { color: #4a5568; font-size: 32px; font-weight: 700; margin-bottom: 8px; }
        .auth-logo p { color: #718096; font-size: 16px; }
        .form-group { margin-bottom: 24px; }
        label { display: block; color: #4a5568; font-size: 14px; font-weight: 600; margin-bottom: 8px; }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            font-size: 15px;
            border: 2px solid #f7dfd4;
            border-radius: 12px;
            background: #fef8f5;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #d4a5a5;
            background: white;
            box-shadow: 0 0 0 4px rgba(212, 165, 165, 0.1);
        }
        .btn-primary {
            width: 100%;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #d4a5a5 0%, #c79a94 100%);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212, 165, 165, 0.5); }
        .role-selector { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .role-option {
            padding: 20px 16px;
            border: 2px solid #f7dfd4;
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fef8f5;
        }
        .role-option:hover, .role-option.selected {
            border-color: #d4a5a5;
            background: white;
            box-shadow: 0 4px 12px rgba(212, 165, 165, 0.2);
        }
        .role-option input { display: none; }
        .role-icon { font-size: 40px; margin-bottom: 12px; }
        .role-label { font-weight: 700; font-size: 16px; color: #4a5568; margin-bottom: 4px; }
        .role-description { font-size: 13px; color: #718096; }
        .text-center { text-align: center; margin-top: 24px; }
        .text-center a { color: #d4a5a5; font-weight: 600; text-decoration: none; }
        .form-text { font-size: 13px; color: #a0aec0; margin-top: 6px; }
        .password-strength { height: 4px; background: #f7dfd4; border-radius: 4px; margin-top: 8px; overflow: hidden; }
        .password-strength-bar { height: 100%; width: 0; transition: all 0.4s ease; border-radius: 4px; }
        .password-strength-bar.weak { width: 33%; background: #d4a5a5; }
        .password-strength-bar.medium { width: 66%; background: #c79a94; }
        .password-strength-bar.strong { width: 100%; background: #86c3a8; }
        @media (max-width: 480px) {
            .auth-card { padding: 36px 28px; }
            .role-selector { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon">🎓</div>
            <h1>LearnHub</h1>
            <p>Create your account to start learning</p>
        </div>
        
        <form method="POST" action="" id="registerForm">
            <?php echo csrf_field(); ?>
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="your.email@example.com" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Create a strong password" required>
                <div class="password-strength"><div class="password-strength-bar" id="strengthBar"></div></div>
                <small class="form-text">Password must be at least 8 characters long</small>
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter your password" required>
            </div>
            
            <div class="form-group">
                <label>I want to register as:</label>
                <div class="role-selector">
                    <label class="role-option selected">
                        <input type="radio" name="role" value="student" checked>
                        <div class="role-icon">🎓</div>
                        <div class="role-label">Student</div>
                        <div class="role-description">Learn from courses</div>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="instructor">
                        <div class="role-icon">👨‍🏫</div>
                        <div class="role-label">Instructor</div>
                        <div class="role-description">Create & teach courses</div>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn-primary">Create Account</button>
        </form>
        
        <div class="text-center">
            <p style="color: #718096;">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
    
    <script>
        document.querySelectorAll('.role-option').forEach(opt => {
            opt.onclick = function() {
                document.querySelectorAll('.role-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked = true;
            };
        });
        
        document.getElementById('password').addEventListener('input', function() {
            const pwd = this.value;
            let strength = 0;
            if (pwd.length >= 8) strength++;
            if (pwd.match(/[a-z]/) && pwd.match(/[A-Z]/)) strength++;
            if (pwd.match(/[0-9]/)) strength++;
            if (pwd.match(/[^a-zA-Z0-9]/)) strength++;
            
            const bar = document.getElementById('strengthBar');
            bar.className = 'password-strength-bar';
            if (strength <= 2) bar.classList.add('weak');
            else if (strength === 3) bar.classList.add('medium');
            else bar.classList.add('strong');
        });
    </script>
</body>
</html>