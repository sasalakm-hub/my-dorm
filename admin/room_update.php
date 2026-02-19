<?php
session_start();
require_once '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. รับค่าที่ส่งมา
    $id = $_POST['id'];
    $room_number = $_POST['room_number'];
    $room_name = $_POST['room_name'];
    $price = $_POST['price'];
    $room_size = $_POST['room_size'];
    $room_type = $_POST['room_type'];
    $status = $_POST['status'];
    $description = $_POST['description'];
    $old_image = $_POST['old_image']; // รูปเดิม

    // 2. เช็คว่ามีการอัปโหลดรูปใหม่ไหม?
    if (!empty($_FILES['room_image']['name'])) {
        // --- กรณีมีรูปใหม่ ---
        $upload_dir = "../uploads/rooms/";
        $file_extension = pathinfo($_FILES["room_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = "room_" . $room_number . "_" . time() . "." . $file_extension;
        $target_file = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file)) {
            // path ใหม่ที่จะลง DB
            $db_image_path = "uploads/rooms/" . $new_filename;
        } else {
            echo "<script>alert('อัปโหลดรูปภาพล้มเหลว'); window.history.back();</script>";
            exit();
        }
    } else {
        // --- กรณีไม่มีรูปใหม่ (ใช้รูปเดิม) ---
        $db_image_path = $old_image;
    }

    // 3. อัปเดตข้อมูลลงฐานข้อมูล
    $sql = "UPDATE rooms SET 
            room_number = '$room_number',
            room_name = '$room_name',
            price = '$price',
            room_size = '$room_size',
            room_type = '$room_type',
            status = '$status',
            description = '$description',
            image = '$db_image_path'
            WHERE id = '$id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('แก้ไขข้อมูลเรียบร้อย'); window.location='manage_rooms.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

} else {
    header("Location: manage_rooms.php");
}
?>