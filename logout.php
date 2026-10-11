<?php
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
    logAudit('LOGOUT', "User {$_SESSION['user_name']} logged out.", $_SESSION['user_id']);
}

session_unset();
session_destroy();

header('Location: ' . BASE_URL . 'login.php');
exit;
