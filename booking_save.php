<?php
session_start();
require_once 'connect.php';

// เช็คว่ามีการส่งข้อมูลแบบ POST มาจริงไหม
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. รับค่าจากฟอร์ม
    $user_id = $_SESSION['user_id'];
    $room_id = $_POST['room_id'];
    $move_in_date = $_POST['move_in_date'];
    
    // แปลงวันที่และเวลาโอนเงิน ให้เป็นรูปแบบที่ Database ชอบ (YYYY-MM-DD HH:MM:SS)
    // แต่ถ้า input type="datetime-local" ค่าที่ส่งมาจะเป็น "2023-10-25T14:30" เราใช้ได้เลย หรือจะตัด T ออกก็ได้
    $transfer_date = $_POST['transfer_date']; 

    // 2. จัดการเรื่องอัปโหลดรูปภาพ (สลิป)
    $upload_dir = "uploads/slips/"; // โฟลเดอร์ปลายทาง
    
    // สร้างชื่อไฟล์ใหม่ เพื่อป้องกันชื่อซ้ำ (เช่น slip_userid_timestamp.jpg)
    $file_extension = pathinfo($_FILES["slip"]["name"], PATHINFO_EXTENSION);
    $new_filename = "slip_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $upload_dir . $new_filename;
    
    $upload_ok = false;

    // เช็คว่าเป็นไฟล์รูปภาพจริงไหม
    $check = getimagesize($_FILES["slip"]["tmp_name"]);
    if($check !== false) {
        // อัปโหลดไฟล์ตัวจริงลง Server
        if (move_uploaded_file($_FILES["slip"]["tmp_name"], $target_file)) {
            $upload_ok = true;
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ'); window.history.back();</script>";
        exit();
    }

    // 3. ถ้าอัปโหลดรูปสำเร็จ ให้บันทึกลง Database
    if ($upload_ok) {
        
        // (A) บันทึกข้อมูลลงตาราง bookings
        // หมายเหตุ: ตรงนี้ผมสมมติว่าคุณเพิ่มคอลัมน์ transfer_date ในตาราง bookings แล้ว (ถ้ายังไม่มี เดี๋ยวบอกวิธีเพิ่มด้านล่างครับ)
        $sql = "INSERT INTO bookings (user_id, room_id, move_in_date, slip_image, status) 
                VALUES ('$user_id', '$room_id', '$move_in_date', '$new_filename', 'pending')";

        if ($conn->query($sql) === TRUE) {
            
            // (B) อัปเดตสถานะห้องพักให้เป็น 'busy' (ไม่ว่าง) ทันที เพื่อกันคนอื่นจองซ้อน
            $update_room = "UPDATE rooms SET status = 'busy' WHERE id = '$room_id'";
            $conn->query($update_room);

            // จองสำเร็จ! ส่งไปหน้าประวัติการจอง (เดี๋ยวเราจะสร้างหน้านี้กันต่อ)
            echo "<script>
                    alert('จองห้องพักเรียบร้อยแล้ว! กรุณารอเจ้าหน้าที่ตรวจสอบการชำระเงิน');
                    window.location = 'booking_history.php'; 
                  </script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
} else {
    // ถ้าเข้าหน้านี้โดยไม่ได้กด submit
    header("Location: index.php");
    exit();
}
?>