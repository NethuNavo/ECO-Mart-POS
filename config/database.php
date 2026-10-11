<?php
/**
 * ECO Mart POS - Database Connection & System Configuration
 * Optimized for XAMPP (Apache + MySQL / MariaDB)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eco_mart_pos');
define('DB_CHARSET', 'utf8mb4');

// System Constants
define('APP_NAME', 'ECO Mart POS');
define('APP_VERSION', '2.5.0');
define('BASE_URL', 'http://localhost/smartmart-pos/');

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                die("<div style='font-family:sans-serif;padding:30px;background:#fff3cd;color:#856404;border:1px solid #ffeeba;border-radius:8px;max-width:600px;margin:50px auto;'>
                    <h3 style='margin-top:0;'>ECO Mart POS - Database Connection Error</h3>
                    <p>Unable to connect to MySQL database <strong>" . DB_NAME . "</strong>.</p>
                    <p><strong>Troubleshooting Steps:</strong></p>
                    <ol>
                        <li>Ensure <strong>Apache</strong> and <strong>MySQL</strong> are running in XAMPP Control Panel.</li>
                        <li>Open <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a> and create database <code>" . DB_NAME . "</code>.</li>
                        <li>Import <code>database/smartmart_pos.sql</code>.</li>
                        <li>Verify credentials in <code>config/database.php</code>.</li>
                    </ol>
                    <small>Technical Error: " . htmlspecialchars($e->getMessage()) . "</small>
                </div>");
            }
        }
        return self::$instance;
    }
}

// Helper function to fetch single setting
function getSetting(string $key, string $default = ''): string {
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $res = $stmt->fetch();
        return $res ? $res['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

// Helper function to log audit events
function logAudit(string $action, string $description, ?int $userId = null): void {
    try {
        $db = Database::getConnection();
        $uid = $userId ?? ($_SESSION['user_id'] ?? null);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$uid, $action, $description, $ip]);
    } catch (Exception $e) {
        // Silently continue if audit logging encounters an issue
    }
}

// Helper to check user authentication and role
function checkAuth(array $allowedRoles = []): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
    if (!empty($allowedRoles) && !in_array($_SESSION['user_role'], $allowedRoles)) {
        header('Location: ' . BASE_URL . 'dashboard.php?error=unauthorized');
        exit;
    }
}

// Helper to format currency (LKR Rs.)
function formatCurrency(float $amount): string {
    return 'Rs. ' . number_format($amount, 2);
}
