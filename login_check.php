<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // ใช้ Prepared Statement (ปลอดภัยกว่า)
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {

        if (password_verify($password, $row['password'])) {

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['firstname'] = $row['firstname'];
            $_SESSION['lastname'] = $row['lastname'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {

                $stmt_room = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND status = 'confirmed'");
                $stmt_room->execute([$row['id']]);
                $result_room = $stmt_room->fetch(PDO::FETCH_ASSOC);

                if ($result_room) {
                    header("Location: user_dashboard.php");
                } else {
                    header("Location: index.php");
                }
            }
            exit();

        } else {
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง'); window.history.back();</script>";
        }

    } else {
        echo "<script>alert('ไม่พบชื่อผู้ใช้นี้ในระบบ'); window.history.back();</script>";
    }
}
?>
