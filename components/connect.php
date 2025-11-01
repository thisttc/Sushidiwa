<?php
$db_name = 'mysql:host=localhost;dbname=168DB_4';
$user_name = '168DB4';
$user_password = 'rcebTuqu';

$conn = new PDO($db_name, $user_name, $user_password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ตรวจสอบว่าฟังก์ชันยังไม่ได้ประกาศก่อนประกาศ
if (!function_exists('unique_id')) {
    function unique_id() {
        $chars = '0123456789abcdefghijklmnopqrstuvWXYZ';
        $charLength = strlen($chars);
        $randomString = '';
        for($i = 0; $i < 20; $i++) {
            $randomString .= $chars[mt_rand(0, $charLength - 1)];
        }
        return $randomString;
    }
}
?>