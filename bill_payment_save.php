<?php
session_start();
require_once 'connect.php';

// เปิดวงเล็บใหญ่ (เช็คว่ามีการกดส่งฟอร์มมา)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $bill_id = $_POST['bill_id'];
    $user_id = $_SESSION['user_id']; 
    
    // ตั้งค่าตัวแปรอัปโหลด
    $upload_dir = "uploads/bill_slips/";
    $file_extension = pathinfo($_FILES["bill_slip"]["name"], PATHINFO_EXTENSION);
    $new_filename = "bill_" . $bill_id . "_" . time() . "." . $file_extension;
    $target_file = $upload_dir . $new_filename;

    // เช็คว่าสร้างโฟลเดอร์หรือยัง ถ้ายังให้สร้าง (กันเหนียว)
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // เปิดวงเล็บย่อย (เช็คการอัปโหลด)
    if (move_uploaded_file($_FILES["bill_slip"]["tmp_name"], $target_file)) {
        
        // อัปเดตข้อมูล: ใส่ชื่อรูป, เปลี่ยนสถานะเป็น pending, บันทึกเวลาจ่าย
        $sql = "UPDATE bills SET 
                slip_image = '$new_filename',
                status = 'pending',
                pay_date = NOW()
                WHERE id = '$bill_id' AND user_id = '$user_id'";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('แจ้งชำระเงินเรียบร้อย! รอการตรวจสอบ'); window.location='user_dashboard.php';</script>";
        } else {
            echo "Error Database: " . $conn->error;
        }

    } else {
        // กรณีอัปโหลดไม่ผ่าน (จะแสดง Error ให้เห็นชัดๆ)
        echo "<h3>เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ</h3>";
        echo "Error Code: " . $_FILES["bill_slip"]["error"] . "<br>";
        echo "Target Path: " . $target_file . "<br>";
        echo "Folder Exists: " . (file_exists($upload_dir) ? 'Yes' : 'No') . "<br>";
        echo "<br><a href='user_dashboard.php'>กลับหน้าหลัก</a>";
        exit();
    } // ปิดวงเล็บย่อย

} // ปิดวงเล็บใหญ่ (Error ของคุณน่าจะเกิดจากลืมบรรทัดนี้ครับ)
?>