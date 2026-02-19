<?php
session_start();
require_once '../connect.php';

if (isset($_GET['id']) && $_SESSION['role'] == 'admin') {
    $id = $_GET['id'];
    // เปลี่ยนสถานะเป็น paid
    $conn->query("UPDATE bills SET status = 'paid' WHERE id = '$id'");
    
    echo "<script>alert('อนุมัติยอดชำระเรียบร้อย!'); window.location='manage_bills.php';</script>";
}
?>