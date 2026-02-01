<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';

$verification_code = sanitize($_GET['code'] ?? '');
$certificate = null;

if (!empty($verification_code)) {
    $sql = "SELECT cert.*, c.title as course_title, u.full_name as student_name
            FROM certificates cert
            JOIN courses c ON cert.course_id = c.id
            JOIN users u ON cert.user_id = u.id
            WHERE cert.verification_code = ?";
    
    $result = db_query($conn, $sql, "s", [$verification_code]);
    
    if ($result && db_num_rows($result) > 0) {
        $certificate = db_fetch_assoc($result);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Certificate - <?php echo SITE_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: linear-gradient(135deg, #fef5f1 0%, #fef8f5 100%);
            color: #2d3748;
        }
        .navbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 16px 0;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .navbar-content { display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand { font-size: 24px; font-weight: 700; color: #d4a5a5; text-decoration: none; }
        .verify-container { padding: 80px 24px; min-height: calc(100vh - 250px); }
        .verify-card {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 48px 40px;
            border: 2px solid #f7dfd4;
        }
        .verify-title {
            text-align: center;
            font-size: clamp(28px, 3vw, 36px);
            font-weight: 800;
            color: #4a5568;
            margin-bottom: 40px;
        }
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
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(212, 165, 165, 0.4); }
        .result-box {
            margin-top: 32px;
            padding: 32px;
            border-radius: 16px;
            border: 2px solid;
        }
        .result-valid {
            background: linear-gradient(135deg, rgba(134, 195, 168, 0.1) 0%, rgba(134, 195, 168, 0.15) 100%);
            border-color: #86c3a8;
        }
        .result-invalid {
            background: linear-gradient(135deg, rgba(212, 165, 165, 0.1) 0%, rgba(212, 165, 165, 0.15) 100%);
            border-color: #d4a5a5;
        }
        .result-title { font-size: 24px; font-weight: 700; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
        .result-title.valid { color: #4a7c59; }
        .result-title.invalid { color: #9c6b65; }
        .cert-details {
            background: white;
            padding: 28px;
            border-radius: 16px;
            margin-top: 20px;
            border: 2px solid #f7dfd4;
        }
        .cert-row {
            display: flex;
            padding: 16px 0;
            border-bottom: 1px solid #f7dfd4;
            gap: 16px;
        }
        .cert-row:last-child { border-bottom: none; }
        .cert-label { font-weight: 700; color: #718096; min-width: 150px; font-size: 14px; }
        .cert-value { color: #4a5568; flex: 1; font-size: 15px; font-weight: 500; }
        .cert-code {
            background: #fef8f5;
            padding: 8px 12px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            border: 1px solid #f7dfd4;
        }
        .footer { background: #4a5568; color: white; padding: 48px 24px 24px; text-align: center; margin-top: 80px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="index.php" class="navbar-brand">🎓 LearnHub</a>
            </div>
        </div>
    </nav>

    <div class="verify-container">
        <div class="verify-card">
            <h1 class="verify-title">🏆 Certificate Verification</h1>

            <form method="GET" action="">
                <div class="form-group">
                    <label>Enter Verification Code</label>
                    <input type="text" name="code" class="form-control" placeholder="CERT-XXXXXXXX" value="<?php echo htmlspecialchars($verification_code); ?>" required autofocus>
                </div>
                <button type="submit" class="btn-primary">Verify Certificate</button>
            </form>

            <?php if (!empty($verification_code)): ?>
                <?php if ($certificate): ?>
                <div class="result-box result-valid">
                    <h3 class="result-title valid">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Valid Certificate
                    </h3>
                    <div class="cert-details">
                        <div class="cert-row">
                            <div class="cert-label">Student Name:</div>
                            <div class="cert-value"><?php echo htmlspecialchars($certificate['student_name']); ?></div>
                        </div>
                        <div class="cert-row">
                            <div class="cert-label">Course:</div>
                            <div class="cert-value"><?php echo htmlspecialchars($certificate['course_title']); ?></div>
                        </div>
                        <div class="cert-row">
                            <div class="cert-label">Issue Date:</div>
                            <div class="cert-value"><?php echo format_date($certificate['generated_at']); ?></div>
                        </div>
                        <div class="cert-row">
                            <div class="cert-label">Verification Code:</div>
                            <div class="cert-value">
                                <span class="cert-code"><?php echo htmlspecialchars($certificate['verification_code']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="result-box result-invalid">
                    <h3 class="result-title invalid">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Invalid Certificate
                    </h3>
                    <p style="margin: 0; color: #9c6b65;">The verification code you entered does not match any certificate in our database.</p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>