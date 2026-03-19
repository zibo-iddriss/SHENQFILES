<?php
/**
 * Security Helper Functions
 * Provides sanitization, validation, and CSRF protection
 */

/**
 * Sanitize user input to prevent XSS attacks
 */
function sanitize_input($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Validate email format
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate username format (alphanumeric and underscore only)
 */
function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

/**
 * Validate strong password (min 8 chars, uppercase, lowercase, number, special char)
 */
function validate_strong_password($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}

/**
 * Validate numeric input
 */
function validate_number($value, $min = 0, $max = null) {
    if (!is_numeric($value)) {
        return false;
    }
    $value = floatval($value);
    if ($value < $min) {
        return false;
    }
    if ($max !== null && $value > $max) {
        return false;
    }
    return true;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Create CSRF token input field
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Sanitize SQL string (use prepared statements when possible)
 */
function sanitize_sql($input) {
    global $conn;
    return $conn->real_escape_string($input);
}

/**
 * Rate limiting - prevent brute force attacks
 */
function check_rate_limit($identifier, $max_attempts = 5, $window = 900) {
    $key = "rate_limit_" . $identifier;
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
    }
    
    $attempt_data = $_SESSION[$key];
    $time_elapsed = time() - $attempt_data['first_attempt'];
    
    // Reset if window expired
    if ($time_elapsed > $window) {
        $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
        return true;
    }
    
    // Check if limit exceeded
    if ($attempt_data['attempts'] >= $max_attempts) {
        return false;
    }
    
    $_SESSION[$key]['attempts']++;
    return true;
}

/**
 * Reset rate limit for identifier
 */
function reset_rate_limit($identifier) {
    $key = "rate_limit_" . $identifier;
    unset($_SESSION[$key]);
}

/**
 * Log security events
 */
function log_security_event($event_type, $details = []) {
    $log_file = __DIR__ . '/logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    
    // Create logs directory if it doesn't exist
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    $log_message = "$timestamp | IP: $ip_address | EVENT: $event_type | DETAILS: " . json_encode($details) . PHP_EOL;
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * Validate file upload
 */
function validate_file_upload($file, $allowed_types = ['.jpg', '.jpeg', '.png', '.pdf'], $max_size = 5242880) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['valid' => false, 'message' => 'No file uploaded'];
    }
    
    $file_info = pathinfo($file['name']);
    $file_ext = '.' . strtolower($file_info['extension']);
    
    if (!in_array($file_ext, $allowed_types)) {
        return ['valid' => false, 'message' => 'File type not allowed'];
    }
    
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'message' => 'File size exceeds limit'];
    }
    
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['valid' => false, 'message' => 'Invalid file upload'];
    }
    
    return ['valid' => true, 'message' => 'File valid'];
}

/**
 * Hash password using bcrypt
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize output to prevent XSS
 */
function safe_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>
