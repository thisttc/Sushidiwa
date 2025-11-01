<?php
include 'session_manager.php';

// ล้างข้อมูลทั้งหมดใน session
$_SESSION = [];

// ลบคุกกี้ session ถ้ามี
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ทำลาย session
session_destroy();

// Redirect ไปหน้า home
header('location: ../home.php');
exit;
?>