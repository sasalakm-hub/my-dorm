<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $_POST['check_username'];
    $email = $_POST['check_email'];
    $phone = $_POST['check_phone'];
    $new_password = $_POST['new_password'];

    // 1. ตรวจสอบข้อมูลว่าตรงกันทั้ง 3 อย่างไหม
    $sql = "SELECT * FROM users WHERE username = '$username' AND email = '$email' AND phone = '$phone'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // ข้อมูลถูกต้อง! เจอผู้ใช้ตัวจริง
        $row = $result->fetch_assoc();
        $user_id = $row['id'];

        // 2. เข้ารหัสรหัสผ่านใหม่
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // 3. อัปเดตรหัสผ่านลงฐานข้อมูล
        $update_sql = "UPDATE users SET password = '$new_password_hashed' WHERE id = '$user_id'";
        
        if ($conn->query($update_sql) === TRUE) {
            echo "<script>alert('เปลี่ยนรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบด้วยรหัสใหม่'); window.location='login.php';</script>";
        } else {
            echo "Error updating record: " . $conn->error;
        }

    } else {
        // ข้อมูลไม่ตรง
        echo "<script>alert('ข้อมูลไม่ถูกต้อง! (ชื่อผู้ใช้, อีเมล หรือเบอร์โทร ไม่ตรงกับในระบบ)'); window.history.back();</script>";
    }
}
?>