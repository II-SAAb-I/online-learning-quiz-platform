<?php

function require_login($redirect_url = null) {
    if (!is_logged_in()) {
        if ($redirect_url) {
            $_SESSION['redirect_after_login'] = $redirect_url;
        } else {
            $_SESSION['redirect_after_login'] = current_url();
        }
        
        set_message('warning', 'Please login to access this page.');
        redirect(SITE_URL . '/login.php');
    }
}

function require_role($required_roles, $redirect_url = null) {
    require_login();
    
    if (!has_role($required_roles)) {
        set_message('error', 'You do not have permission to access this page.');
        
        if ($redirect_url) {
            redirect($redirect_url);
        } else {
            redirect(get_dashboard_url());
        }
    }
}

function require_student() {
    require_role(ROLE_STUDENT);
}

function require_instructor() {
    require_role([ROLE_INSTRUCTOR, ROLE_ADMIN]);
}

function require_admin() {
    require_role(ROLE_ADMIN);
}

function redirect_if_logged_in($redirect_url = null) {
    if (is_logged_in()) {
        if ($redirect_url) {
            redirect($redirect_url);
        } else {
            redirect(get_dashboard_url());
        }
    }
}

function get_dashboard_url() {
    if (!is_logged_in()) {
        return SITE_URL . '/index.php';
    }
    
    $role = get_user_role();
    
    switch ($role) {
        case ROLE_ADMIN:
            return SITE_URL . '/admin/dashboard.php';
        case ROLE_INSTRUCTOR:
            return SITE_URL . '/instructor/dashboard.php';
        case ROLE_STUDENT:
        default:
            return SITE_URL . '/student/dashboard.php';
    }
}

function can_access_course($conn, $course_id, $user_id = null) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_id = $user_id ?? get_user_id();
    $role = get_user_role();
    
    if ($role === ROLE_ADMIN) {
        return true;
    }
    
    $sql = "SELECT id FROM courses WHERE id = ? AND created_by = ?";
    $result = db_query($conn, $sql, "ii", [$course_id, $user_id]);
    
    if ($result && db_num_rows($result) > 0) {
        return true;
    }
    
    if ($role === ROLE_STUDENT) {
        $sql = "SELECT id FROM enrollments WHERE course_id = ? AND user_id = ?";
        $result = db_query($conn, $sql, "ii", [$course_id, $user_id]);
        
        return $result && db_num_rows($result) > 0;
    }
    
    return false;
}

function owns_resource($conn, $table, $resource_id, $owner_field = 'created_by', $user_id = null) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_id = $user_id ?? get_user_id();
    $role = get_user_role();
    
    if ($role === ROLE_ADMIN) {
        return true;
    }
    
    $sql = "SELECT id FROM $table WHERE id = ? AND $owner_field = ?";
    $result = db_query($conn, $sql, "ii", [$resource_id, $user_id]);
    
    return $result && db_num_rows($result) > 0;
}

function is_enrolled($conn, $course_id, $user_id = null) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_id = $user_id ?? get_user_id();
    
    $sql = "SELECT id FROM enrollments WHERE course_id = ? AND user_id = ?";
    $result = db_query($conn, $sql, "ii", [$course_id, $user_id]);
    
    return $result && db_num_rows($result) > 0;
}

function authenticate_user($conn, $email, $password) {
    $sql = "SELECT id, email, full_name, password, role, is_active 
            FROM users 
            WHERE email = ?";
    
    $result = db_query($conn, $sql, "s", [$email]);
    
    if ($result && db_num_rows($result) == 1) {
        $user = db_fetch_assoc($result);
        
        if (!$user['is_active']) {
            return false;
        }
        
        if (verify_password($password, $user['password'])) {
            unset($user['password']);
            unset($user['is_active']);
            return $user;
        }
    }
    
    return false;
}

function email_exists($conn, $email, $exclude_user_id = null) {
    if ($exclude_user_id) {
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $result = db_query($conn, $sql, "si", [$email, $exclude_user_id]);
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        $result = db_query($conn, $sql, "s", [$email]);
    }
    
    return $result && db_num_rows($result) > 0;
}

function register_user($conn, $data) {
    if (empty($data['full_name']) || empty($data['email']) || empty($data['password'])) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!validate_email($data['email'])) {
        return ['success' => false, 'message' => 'Invalid email address'];
    }
    
    if (email_exists($conn, $data['email'])) {
        return ['success' => false, 'message' => 'Email address already registered'];
    }
    
    $password_validation = validate_password($data['password']);
    if (!$password_validation['valid']) {
        return ['success' => false, 'message' => $password_validation['message']];
    }
    
    $hashed_password = hash_password($data['password']);
    
    $role = $data['role'] ?? ROLE_STUDENT;
    
    $sql = "INSERT INTO users (full_name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
    $result = db_query($conn, $sql, "ssss", [
        $data['full_name'],
        $data['email'],
        $hashed_password,
        $role
    ]);
    
    if ($result) {
        $user_id = db_insert_id($conn);
        return [
            'success' => true,
            'message' => 'Registration successful',
            'user_id' => $user_id
        ];
    }
    
    return ['success' => false, 'message' => 'Registration failed. Please try again.'];
}

function login_user($conn, $email, $password, $remember_me = false) {
    $user = authenticate_user($conn, $email, $password);
    
    if ($user) {
        set_user_session($user);
        
        if ($remember_me) {
            set_remember_me_cookie($user['id'], $user['email']);
        }
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}

function logout_user() {
    clear_remember_me_cookie();
    destroy_session();
}

?>