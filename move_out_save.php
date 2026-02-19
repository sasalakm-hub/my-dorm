<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user_id = $_SESSION['user_id'];
    $room_id = $_POST['room_id'];
    $move_out_date = $_POST['move_out_date'];
    $reason = $_POST['reason'];
    // รับค่าเพิ่ม
    $bank_name = $_POST['bank_name'];
    $bank_account = $_POST['bank_account'];
    $bank_owner = $_POST['bank_owner'];

    // Insert ข้อมูลครบ
    $sql = "INSERT INTO move_out_requests (user_id, room_id, move_out_date, reason, bank_name, bank_account, bank_owner, status) 
            VALUES ('$user_id', '$room_id', '$move_out_date', '$reason', '$bank_name', '$bank_account', '$bank_owner', 'pending')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('ส่งเรื่องแจ้งย้ายออกเรียบร้อยแล้ว'); window.location='move_out.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>