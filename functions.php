<?php
/**
 * Global Helper Functions
 * 
 * Common utility functions used throughout the application
 */

/**
 * Sanitize input data
 * 
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirect to another page
 * 
 * @param string $url - URL to redirect to
 * @param int $status_code - HTTP status code (default: 302)
 */
function redirect($url, $status_code = 302) {
    header("Location: " . $url, true, $status_code);
    exit();
}

/**
 * Set flash message in session
 * 
 * @param string $type - Message type (success, error, warning, info)
 * @param string $message - Message content
 */
function set_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message from session
 * 
 * @return array|null - Flash message array or null
 */
function get_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 * 
 * @return string - HTML for flash message
 */
function display_message() {
    $message = get_message();
    if ($message) {
        $type_class = '';
        switch($message['type']) {
            case 'success':
                $type_class = 'alert-success';
                break;
            case 'error':
                $type_class = 'alert-danger';
                break;
            case 'warning':
                $type_class = 'alert-warning';
                break;
            case 'info':
                $type_class = 'alert-info';
                break;
        }
        
        echo '<div class="alert ' . $type_class . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

/**
 * Generate CSRF token
 * 
 * @return string - CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token - Token to verify
 * @return bool - True if valid, false otherwise
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check if token has expired
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field
 * 
 * @return string - Hidden input HTML
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validate email address
 * 
 * @param string $email - Email to validate
 * @return bool - True if valid, false otherwise
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * 
 * @param string $password - Password to validate
 * @return array - ['valid' => bool, 'message' => string]
 */
function validate_password($password) {
    $errors = [];
    
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $errors[] = "Password must be at least " . MIN_PASSWORD_LENGTH . " characters long";
    }
    
    if (REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if (REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (REQUIRE_SPECIAL_CHAR && !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return [
        'valid' => empty($errors),
        'message' => implode(', ', $errors)
    ];
}

/**
 * Hash password
 * 
 * @param string $password - Plain text password
 * @return string - Hashed password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * 
 * @param string $password - Plain text password
 * @param string $hash - Hashed password
 * @return bool - True if match, false otherwise
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Upload file
 * 
 * @param array $file - $_FILES array element
 * @param string $destination - Upload directory
 * @param array $allowed_types - Allowed MIME types
 * @return array - ['success' => bool, 'filename' => string, 'message' => string]
 */
function upload_file($file, $destination, $allowed_types = []) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed'];
    }
    
    // Check file type
    $file_type = mime_content_type($file['tmp_name']);
    if (!empty($allowed_types) && !in_array($file_type, $allowed_types)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $destination . '/' . $filename;
    
    // Create destination directory if it doesn't exist
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'message' => 'File uploaded successfully'];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Delete file
 * 
 * @param string $filepath - Path to file
 * @return bool - True if deleted, false otherwise
 */
function delete_file($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Format date
 * 
 * @param string $date - Date string
 * @param string $format - Date format (default: DATE_FORMAT)
 * @return string - Formatted date
 */
function format_date($date, $format = DATE_FORMAT) {
    return date($format, strtotime($date));
}

/**
 * Time ago function
 * 
 * @param string $datetime - Datetime string
 * @return string - Time ago string
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return format_date($datetime);
    }
}

/**
 * Truncate text
 * 
 * @param string $text - Text to truncate
 * @param int $length - Maximum length
 * @param string $suffix - Suffix to add (default: '...')
 * @return string - Truncated text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Slugify string (convert to URL-friendly format)
 * 
 * @param string $text - Text to slugify
 * @return string - Slugified text
 */
function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

/**
 * Generate random string
 * 
 * @param int $length - Length of string
 * @return string - Random string
 */
function generate_random_string($length = 16) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get user IP address
 * 
 * @return string - IP address
 */
function get_ip_address() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Get current page URL
 * 
 * @return string - Current URL
 */
function current_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if user is logged in
 * 
 * @return bool - True if logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 * 
 * @return int|null - User ID or null
 */
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * 
 * @return string|null - User role or null
 */
function get_user_role() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if user has specific role
 * 
 * @param string|array $roles - Role(s) to check
 * @return bool - True if user has role, false otherwise
 */
function has_role($roles) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_role = get_user_role();
    
    if (is_array($roles)) {
        return in_array($user_role, $roles);
    }
    
    return $user_role === $roles;
}

/**
 * Calculate percentage
 * 
 * @param int $completed - Completed items
 * @param int $total - Total items
 * @return float - Percentage
 */
function calculate_percentage($completed, $total) {
    if ($total == 0) {
        return 0;
    }
    return round(($completed / $total) * 100, 2);
}

/**
 * Format duration in minutes to human readable
 * 
 * @param int $minutes - Duration in minutes
 * @return string - Formatted duration
 */
function format_duration($minutes) {
    if ($minutes < 60) {
        return $minutes . ' min';
    }
    
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($mins == 0) {
        return $hours . ' hour' . ($hours > 1 ? 's' : '');
    }
    
    return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $mins . ' min';
}

/**
 * Format file size
 * 
 * @param int $bytes - File size in bytes
 * @return string - Formatted file size
 */
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Debug function - var_dump with pre tags
 *
 * @param mixed $data - Data to dump
 */
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

/**
 * Convert video URL to embed format for iframe
 *
 * @param string $url - Video URL
 * @return string - Embed URL or original URL if not recognized
 */
function convert_to_embed_url($url) {
    if (empty($url)) {
        return $url;
    }

    // YouTube URL patterns
    if (preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches)) {
        $video_id = $matches[1];
        return 'https://www.youtube.com/embed/' . $video_id;
    }

    // Vimeo URL patterns
    if (preg_match('/(?:https?:\/\/)?(?:www\.)?(?:vimeo\.com\/)(?:.*#|.*\/videos\/|.*\/|channels\/.*\/|groups\/.*\/videos\/|album\/.*\/video\/|video\/)?([0-9]+)(?:$|\/|\?)/i', $url, $matches)) {
        $video_id = $matches[1];
        return 'https://player.vimeo.com/video/' . $video_id;
    }

    // If it's already an embed URL, return as-is
    if (preg_match('/(?:youtube\.com\/embed|vimeo\.com\/video)/i', $url)) {
        return $url;
    }

    // For other video platforms or direct video files, return as-is
    return $url;
}

/**
 * Extract video ID from YouTube URL
 *
 * @param string $url - YouTube URL
 * @return string|null - Video ID or null if not found
 */
function extract_youtube_video_id($url) {
    if (empty($url)) {
        return null;
    }

    // YouTube URL patterns
    if (preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches)) {
        return $matches[1];
    }

    return null;
}

/**
 * Get YouTube video duration using YouTube Data API v3
 *
 * @param string $video_id - YouTube video ID
 * @return int|null - Duration in minutes or null if failed
 */
function get_youtube_video_duration($video_id) {
    if (empty($video_id) || empty(YOUTUBE_API_KEY)) {
        return null;
    }

    $api_url = "https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key=" . YOUTUBE_API_KEY . "&part=contentDetails";

    // Use cURL to make API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || empty($response)) {
        return null;
    }

    $data = json_decode($response, true);

    if (!$data || !isset($data['items'][0]['contentDetails']['duration'])) {
        return null;
    }

    // Parse ISO 8601 duration (PT4M13S = 4 minutes 13 seconds)
    $duration = $data['items'][0]['contentDetails']['duration'];

    // Convert ISO 8601 duration to seconds
    $interval = new DateInterval($duration);
    $seconds = ($interval->h * 3600) + ($interval->i * 60) + $interval->s;

    // Convert to minutes (round up)
    return ceil($seconds / 60);
}

/**
 * Get video duration from URL (supports YouTube, Vimeo)
 *
 * @param string $url - Video URL
 * @return int|null - Duration in minutes or null if failed
 */
function get_video_duration($url) {
    if (empty($url)) {
        return null;
    }

    // YouTube
    $video_id = extract_youtube_video_id($url);
    if ($video_id) {
        return get_youtube_video_duration($video_id);
    }

    // Vimeo - would need Vimeo API implementation
    // For now, return null for Vimeo
    if (preg_match('/(?:https?:\/\/)?(?:www\.)?(?:vimeo\.com\/)(?:.*#|.*\/videos\/|.*\/|channels\/.*\/|groups\/.*\/videos\/|album\/.*\/video\/|video\/)?([0-9]+)(?:$|\/|\?)/i', $url)) {
        // Vimeo API implementation would go here
        return null;
    }

    return null;
}

?>
