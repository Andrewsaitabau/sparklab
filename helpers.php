<?php
// helpers.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Escape HTML output safely
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Check if user is logged in
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return !empty($_SESSION['user']);
    }
}

// Get logged in user data
if (!function_exists('current_user')) {
    function current_user() {
        return $_SESSION['user'] ?? null;
    }
}

// Redirect helper
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

// Require login for protected pages
if (!function_exists('require_login')) {
    function require_login() {
        if (!is_logged_in()) {
            redirect('login.php');
        }
    }
}

// Require specific role (e.g., admin)
if (!function_exists('require_role')) {
    function require_role($role) {
        require_login();
        if ((current_user()['role'] ?? '') !== $role) {
            die('Access denied.');
        }
    }
}

// CSRF token generation
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// Verify CSRF token - FIXED VERSION
if (!function_exists('verify_csrf')) {
    function verify_csrf() {
        $token = $_POST['csrf'] ?? $_GET['csrf'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(400);
            die('Invalid CSRF token');
        }
    }
}

// Alternative CSRF validation function for forms that don't need to die()
if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token($token) {
        return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Get USD→KES rate from settings table
if (!function_exists('get_setting_rate')) {
    function get_setting_rate($pdo) {
        $stmt = $pdo->query("SELECT usd_to_kes FROM settings LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? floatval($row['usd_to_kes']) : 150; // default 150 if table is empty
    }
}

// Flash message functions - FIXED VERSION
if (!function_exists('flash')) {
    function flash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
}

if (!function_exists('get_flash')) {
    function get_flash($type) {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
}

// Display flash messages (convenience function) - FIXED VERSION
if (!function_exists('display_flash_messages')) {
    function display_flash_messages() {
        $types = ['success', 'error', 'warning', 'info'];
        foreach ($types as $type) {
            $message = get_flash($type);
            if ($message) {
                $alert_class = $type === 'error' ? 'danger' : $type;
                echo '<div class="alert alert-' . e($alert_class) . ' alert-dismissible fade show" role="alert">';
                echo e($message);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
            }
        }
    }
}

// Simple flash message setting for success/error (common use case)
if (!function_exists('set_flash')) {
    function set_flash($type, $message) {
        $_SESSION[$type] = $message;
    }
}

// Get simple flash message
if (!function_exists('get_simple_flash')) {
    function get_simple_flash($type) {
        if (isset($_SESSION[$type])) {
            $message = $_SESSION[$type];
            unset($_SESSION[$type]);
            return $message;
        }
        return null;
    }
}

// Simple display function for common flash messages
if (!function_exists('display_simple_flash')) {
    function display_simple_flash() {
        $success = get_simple_flash('success');
        $error = get_simple_flash('error');
        
        if ($success) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
            echo e($success);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
        
        if ($error) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo e($error);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
    }
}
?>