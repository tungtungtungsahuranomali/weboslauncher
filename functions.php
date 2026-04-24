<?php

// FUNCTIONS.PHP - v15.4 

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}


if (!isset($db)) {
    require_once __DIR__ . '/config.php';
}


if (!function_exists('log_error')) {
    function log_error($message)
    {
        error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . $message . "\n", 3, __DIR__ . '/../php-error.log');
    }
}

if (!function_exists('get_full_url')) {
    function get_full_url($path)
    {
        if (empty($path))
            return '';

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }
}


function redirect($url)
{
    header("Location: $url");
    exit;
}


function clean($str)
{
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}


function is_admin_logged_in()
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}


function require_admin_login()
{
    if (!is_admin_logged_in()) {
        redirect('admin.php?page=login');
    }
}


function formatDate($dateStr)
{
    if (empty($dateStr))
        return '-';
    return date('d M Y, H:i', strtotime($dateStr));
}


function get_setting($key)
{
    global $db;
    try {
        $stmt = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : null;
    } catch (PDOException $e) {
        log_error("Get setting error: " . $e->getMessage());
        return null;
    }
}


function set_setting($key, $value)
{
    global $db;
    try {
        $stmt = $db->prepare("
            INSERT INTO global_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$key, $value]);
        return true;
    } catch (PDOException $e) {
        log_error("Set setting error: " . $e->getMessage());
        return false;
    }
}


function get_visible_apps()
{
    global $db;
    try {
        $stmt = $db->query("SELECT * FROM system_apps WHERE is_visible = 1 ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        log_error("Get visible apps error: " . $e->getMessage());
        return [];
    }
}


function flash($key, $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
    }
    return null;
}


/**
 * Ambil daftar permissions untuk admin tertentu
 */
function get_user_permissions($admin_id)
{
    try {
        $db = init_db_connection();
        if (!$db)
            return [];
        $stmt = $db->prepare("SELECT page_key, allowed FROM admin_permissions WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        $perms = [];
        while ($row = $stmt->fetch()) {
            $perms[$row['page_key']] = (bool) $row['allowed'];
        }
        return $perms;
    } catch (PDOException $e) {
        log_error("Get permissions error: " . $e->getMessage());
        return [];
    }
}


/**
 * Cek apakah user yang login punya akses ke halaman tertentu
 */
function has_permission($page)
{
    // Superadmin selalu punya akses
    if (($_SESSION['admin_role'] ?? '') === 'superadmin') {
        return true;
    }

    // Halaman yang selalu boleh diakses
    $always_allowed = ['login', 'register', 'logout', 'dashboard'];
    if (in_array($page, $always_allowed)) {
        return true;
    }

    $admin_id = $_SESSION['admin_id'] ?? 0;
    if (!$admin_id)
        return false;

    // Cache permissions di session
    if (!isset($_SESSION['admin_permissions'])) {
        $_SESSION['admin_permissions'] = get_user_permissions($admin_id);
    }

    return !empty($_SESSION['admin_permissions'][$page]);
}
?>