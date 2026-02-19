<?php
session_start();
require_once 'connect.php'; // เรียกไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามีการกดปุ่ม Submit มาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. รับค่าจากฟอร์ม
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 2. เช็คว่ารหัสผ่านตรงกันไหม
    if ($password !== $confirm_password) {
        echo "<script>alert('รหัสผ่านยืนยันไม่ตรงกัน!'); window.history.back();</script>";
        exit();
    }

    // 3. เช็คว่า Username นี้มีคนใช้ไปหรือยัง?
    $check_sql = "SELECT username FROM users WHERE username = '$username'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<script>alert('ชื่อผู้ใช้นี้ (Username) ถูกใช้งานแล้ว กรุณาเปลี่ยนชื่อใหม่'); window.history.back();</script>";
        exit();
    }

    // 4. เข้ารหัสรหัสผ่าน (เพื่อความปลอดภัย)
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // 5. บันทึกลงฐานข้อมูล (กำหนด role เป็น user เสมอ)
    $sql = "INSERT INTO users (firstname, lastname, email , username, phone, password, role) 
            VALUES ('$firstname', '$lastname', '$$email' , '$username', '$phone', '$password_hashed', 'user')";

    if ($conn->query($sql) === TRUE) {
        // สมัครสำเร็จ -> ส่งไปหน้า Login
        echo "<script>alert('สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ'); window.location='login.php';</script>";
    } else {
        // สมัครไม่สำเร็จ -> แสดง Error
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>