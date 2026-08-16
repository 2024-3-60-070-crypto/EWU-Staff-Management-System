<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "staff_management";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(generate_csrf_token()) . '">';
}

function csrf_check() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid security token. Please go back and try again.");
    }
}

function require_login() {
    if (empty($_SESSION['staff_id'])) {
        header("Location: login.php");
        exit;
    }
}

function require_admin() {
    require_login();
    if (empty($_SESSION['is_admin'])) {
        header("Location: home.php?error=1");
        exit;
    }
}

function is_admin() {
    return !empty($_SESSION['is_admin']);
}
