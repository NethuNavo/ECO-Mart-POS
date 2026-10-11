<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Check login if requested
if (!isset($skipAuth) || !$skipAuth) {
    checkAuth();
}

$storeName = getSetting('store_name', 'ECO Mart');
if (stripos($storeName, 'SmartMart') !== false) {
    $storeName = str_ireplace('SmartMart', 'ECO Mart', $storeName);
}
$themeMode = $_SESSION['theme'] ?? getSetting('theme_mode', 'light');

// Notification count
$unreadNotifs = 0;
try {
    $db = Database::getConnection();
    $notifStmt = $db->query("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0");
    $unreadNotifs = $notifStmt->fetch()['count'] ?? 0;
} catch (Exception $e) {
    $unreadNotifs = 0;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= htmlspecialchars($themeMode) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?><?= htmlspecialchars($storeName) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --eco-primary: #245C3A;
            --eco-cream: #F8F5E9;
            --eco-accent: #B7D94C;
            --eco-text: #263238;
            --primary-emerald: var(--eco-primary);
            --primary-emerald-dark: #19452B;
            --primary-emerald-light: #EAF0E5;
            --pos-bg: var(--eco-cream);
            --bs-success: var(--eco-primary);
            --bs-success-rgb: 36, 92, 58;
            --bs-primary: var(--eco-primary);
            --bs-primary-rgb: 36, 92, 58;
            --bs-warning: var(--eco-accent);
            --bs-warning-rgb: 183, 217, 76;
            --bs-body-color: var(--eco-text);
            --bs-body-bg: var(--eco-cream);
            --bs-link-color: var(--eco-primary);
            --bs-link-hover-color: #19452B;
            --font-main: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }
        body {
            font-family: var(--font-main);
            background-color: var(--eco-cream);
            color: var(--eco-text);
        }
        [style*="#f8fafc"], [style*="#f1f5f9"] { background-color: var(--eco-cream) !important; }
        .text-success, .navbar-brand { color: var(--eco-primary) !important; }
        .text-dark { color: var(--eco-text) !important; }
        .btn-success, .bg-success, .badge-pos { background-color: var(--eco-primary) !important; border-color: var(--eco-primary) !important; }
        .btn-outline-success { color: var(--eco-primary); border-color: var(--eco-primary); }
        .btn-outline-success:hover { background-color: var(--eco-primary); border-color: var(--eco-primary); color: #fff; }
        .bg-success-subtle { background-color: #EAF0E5 !important; }
        .border-success-subtle { border-color: #C8D8C3 !important; }
        .bg-warning-subtle { background-color: #EFF5D7 !important; }
        .border-warning-subtle { border-color: #D5E5A0 !important; }
        .text-success-emphasis { color: var(--eco-primary) !important; }
        .btn-warning, .bg-warning { background-color: var(--eco-accent) !important; border-color: var(--eco-accent) !important; color: var(--eco-text) !important; }
        .btn-outline-warning { color: var(--eco-text); border-color: var(--eco-accent); }
        .btn-outline-warning:hover { background-color: var(--eco-accent); border-color: var(--eco-accent); color: var(--eco-text); }
        .text-warning, .text-warning-emphasis { color: var(--eco-text) !important; }
        [data-bs-theme="dark"] body {
            background-color: #0f172a;
            color: #f8fafc;
        }
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--primary-emerald) !important;
        }
        .sidebar {
            width: 260px;
            min-height: calc(100vh - 65px);
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        [data-bs-theme="dark"] .sidebar {
            background: #1e293b;
            border-color: #334155;
        }
        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            border-radius: 8px;
            color: #475569;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 4px;
            transition: all 0.15s ease-in-out;
        }
        [data-bs-theme="dark"] .nav-link-custom {
            color: #94a3b8;
        }
        .nav-link-custom:hover, .nav-link-custom.active {
            background-color: #EAF0E5;
            color: var(--eco-primary);
            font-weight: 600;
        }
        [data-bs-theme="dark"] .nav-link-custom:hover, [data-bs-theme="dark"] .nav-link-custom.active {
            background-color: rgba(5, 150, 105, 0.15);
            color: #34d399;
        }
        .nav-link-custom i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 10px;
        }
        .badge-pos {
            background: var(--eco-primary);
            color: #fff;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .card-kpi {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .card-kpi:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.06);
        }
        .currency-font {
            font-family: var(--font-mono);
            font-weight: 700;
        }
    </style>
</head>
<body>
