<?php
/**
 * Session Management
 * 
 * Initialize and configure session settings
 */

// Prevent direct access
if (session_status() == PHP_SESSION_NONE) {
    // Session configuration for security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_samesite', 'Lax');
    
    // Session lifetime
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
    ini_set('session.cookie_lifetime', 0); // Until browser closes
    
    // Start session
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
    
    // Check for session timeout
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            // Session has expired
            session_unset();
            session_destroy();
            session_start();
            
            // Set timeout message
            $_SESSION['timeout_message'] = 'Your session has expired. Please login again.';
        }
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}

/**
 * Destroy session and logout user
 */
function destroy_session() {
    // Unset all session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Check if session is valid
 * 
 * @return bool
 */
function is_session_valid() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

/**
 * Get session user data
 * 
 * @return array|null
 */
function get_session_user() {
    if (!is_session_valid()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role']
    ];
}

/**
 * Set user session data after login
 * 
 * @param array $user - User data from database
 */
function set_user_session($user) {
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['user_ip'] = get_ip_address();
}

/**
 * Check for "Remember Me" cookie and auto-login
 * 
 * @param mysqli $conn - Database connection
 * @return bool - True if auto-login successful
 */
function check_remember_me($conn) {
    if (isset($_COOKIE['remember_me']) && !is_logged_in()) {
        $token = $_COOKIE['remember_me'];
        
        // Verify token (you would store this in a remember_tokens table)
        // This is a simplified version
        $sql = "SELECT id, email, full_name, role FROM users WHERE MD5(CONCAT(id, email)) = ? AND is_active = 1";
        $result = db_query($conn, $sql, "s", [$token]);
        
        if ($result && db_num_rows($result) == 1) {
            $user = db_fetch_assoc($result);
            set_user_session($user);
            return true;
        }
    }
    
    return false;
}

/**
 * Set "Remember Me" cookie
 * 
 * @param int $user_id - User ID
 * @param string $email - User email
 */
function set_remember_me_cookie($user_id, $email) {
    $token = md5($user_id . $email);
    setcookie('remember_me', $token, time() + REMEMBER_ME_DURATION, '/', '', false, true);
}

/**
 * Clear "Remember Me" cookie
 */
function clear_remember_me_cookie() {
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
        unset($_COOKIE['remember_me']);
    }
}

?>