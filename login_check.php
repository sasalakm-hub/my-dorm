<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. ค้นหาชื่อผู้ใช้ใน Database
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 2. ตรวจสอบรหัสผ่าน (เทียบรหัสที่กรอก กับ รหัสที่เข้ารหัสใน DB)
        if (password_verify($password, $row['password'])) {
            
            // --- ล็อกอินผ่าน! เก็บข้อมูลลง Session ---
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['firstname'] = $row['firstname'];
            $_SESSION['lastname'] = $row['lastname'];
            $_SESSION['role'] = $row['role'];

            // 3. แยกย้ายไปตามบทบาท (Admin ไปหลังบ้าน / User ไปหน้าบ้าน)
            if($row['role'] == 'admin'){
                header("Location: admin/dashboard.php"); 
            } else {
                // ⭐ [แก้ไขใหม่] เช็คก่อนว่า User คนนี้ มีการจองที่ "อนุมัติแล้ว" หรือยัง?
                $user_id = $row['id'];
                $sql_check_room = "SELECT * FROM bookings WHERE user_id = '$user_id' AND status = 'confirmed'";
                $result_room = $conn->query($sql_check_room);

                if ($result_room->num_rows > 0) {
                    // ถ้ามีห้องแล้ว -> ไปหน้า Dashboard ส่วนตัว
                    header("Location: user_dashboard.php");
                } else {
                    // ถ้ายังไม่มีห้อง -> ไปหน้าเลือกห้องพัก (Index)
                    header("Location: index.php");
                }
            }
            exit();

        } else {
            // รหัสผิด
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง'); window.history.back();</script>";
        }
    } else {
        // ไม่เจอชื่อผู้ใช้
        echo "<script>alert('ไม่พบชื่อผู้ใช้นี้ในระบบ'); window.history.back();</script>";
    }
}
?>