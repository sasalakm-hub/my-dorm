<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user_id = $_SESSION['user_id'];
    $room_id = $_POST['room_id'];
    $topic = $_POST['topic'];
    $description = $_POST['description'];
    
    // จัดการรูปภาพ (คล้ายๆ ตอนจองห้อง)
    $upload_dir = "uploads/repairs/";
    $new_filename = "";

    // ถ้ามีการแนบรูปมา
    if (!empty($_FILES["repair_image"]["name"])) {
        $file_extension = pathinfo($_FILES["repair_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = "repair_" . time() . "_" . $user_id . "." . $file_extension;
        $target_file = $upload_dir . $new_filename;
        
        if (!move_uploaded_file($_FILES["repair_image"]["tmp_name"], $target_file)) {
            echo "<script>alert('อัปโหลดรูปภาพไม่สำเร็จ'); window.history.back();</script>";
            exit();
        }
    }

    // บันทึกลง Database
    $sql = "INSERT INTO maintenance_requests (user_id, room_id, topic, description, image, status) 
            VALUES ('$user_id', '$room_id', '$topic', '$description', '$new_filename', 'pending')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('ส่งเรื่องแจ้งซ่อมเรียบร้อย! เจ้าหน้าที่จะรีบดำเนินการ'); window.location='maintenance.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>