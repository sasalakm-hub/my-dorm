<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. รับค่าจากฟอร์ม
    $firstname = $_POST['firstname'] ?? '';
    $lastname  = $_POST['lastname'] ?? '';
    $email     = $_POST['email'] ?? '';
    $username  = $_POST['username'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $password  = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 2. เช็ครหัสผ่านตรงกันไหม
    if ($password !== $confirm_password) {
        echo "<script>alert('รหัสผ่านยืนยันไม่ตรงกัน!'); window.history.back();</script>";
        exit();
    }

    try {

        // 3. เช็คว่า username ซ้ำไหม
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('ชื่อผู้ใช้นี้ถูกใช้งานแล้ว กรุณาเปลี่ยนใหม่'); window.history.back();</script>";
            exit();
        }

        // 4. เข้ารหัสรหัสผ่าน
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // 5. INSERT ข้อมูลแบบ PDO
        $stmt = $conn->prepare("
            INSERT INTO users 
            (firstname, lastname, email, username, phone, password, role)
            VALUES
            (:firstname, :lastname, :email, :username, :phone, :password, 'user')
        ");

        $stmt->execute([
            ':firstname' => $firstname,
            ':lastname'  => $lastname,
            ':email'     => $email,
            ':username'  => $username,
            ':phone'     => $phone,
            ':password'  => $password_hashed
        ]);

        echo "<script>alert('สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ'); window.location='login.php';</script>";

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
