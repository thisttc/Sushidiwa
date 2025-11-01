<?php
class SessionManager {
    
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['user_id']);
    }
    
    public static function getUserId() {
        self::startSession();
        return $_SESSION['user_id'] ?? '';
    }
    
    public static function getUserData() {
        self::startSession();
        return [
            'user_id' => $_SESSION['user_id'] ?? '',
            'user_email' => $_SESSION['user_email'] ?? '',
            'user_name' => $_SESSION['user_name'] ?? ''
        ];
    }
    
    public static function requireLogin($redirectTo = 'login.php') {
        if (!self::isLoggedIn()) {
            header('location: ' . $redirectTo);
            exit;
        }
    }
}

// เริ่ม session อัตโนมัติเมื่อ include ไฟล์นี้
SessionManager::startSession();
?>