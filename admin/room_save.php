<?php
session_start();
require_once '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. รับค่าจากฟอร์ม
    $room_number = $_POST['room_number'];
    $room_name = $_POST['room_name'];
    $price = $_POST['price'];
    $room_size = $_POST['room_size'];
    $room_type = $_POST['room_type'];
    $description = $_POST['description'];
    
    // 2. จัดการรูปภาพ
    $upload_dir = "../uploads/rooms/"; // เก็บไว้ในโฟลเดอร์ uploads/rooms/
    
    // สร้างชื่อไฟล์รูปใหม่ (room_เลขห้อง_เวลา.jpg)
    $file_extension = pathinfo($_FILES["room_image"]["name"], PATHINFO_EXTENSION);
    $new_filename = "room_" . $room_number . "_" . time() . "." . $file_extension;
    $target_file = $upload_dir . $new_filename;
    
    // Path ที่จะบันทึกลง Database (ต้องเอา ../ ออก เพื่อให้เรียกใช้จากหน้าบ้านได้ง่าย)
    $db_image_path = "uploads/rooms/" . $new_filename;

    if (move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file)) {
        
        // 3. บันทึกลงฐานข้อมูล
        $sql = "INSERT INTO rooms (room_number, room_name, price, room_size, room_type, description, image, status) 
                VALUES ('$room_number', '$room_name', '$price', '$room_size', '$room_type', '$description', '$db_image_path', 'available')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('เพิ่มห้องพักสำเร็จ!'); window.location='manage_rooms.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

    } else {
        echo "<script>alert('อัปโหลดรูปภาพไม่สำเร็จ'); window.history.back();</script>";
    }
}
?>