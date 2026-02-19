<?php
session_start();
require_once '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. แยกค่า user_id และ room_id
    $refs = explode(',', $_POST['booking_ref']);
    $user_id = $refs[0];
    $room_id = $refs[1];
    
    $month = $_POST['month']; // format: 2026-02
    
    // 2. ดึงราคาห้องจริงจาก Database (เพื่อความปลอดภัย ไม่เชื่อค่าจากหน้าเว็บอย่างเดียว)
    $sql_room = "SELECT price FROM rooms WHERE id = '$room_id'";
    $res_room = $conn->query($sql_room);
    $price_room = $res_room->fetch_assoc()['price'];

    // 3. รับค่ามิเตอร์
    $elec_old = $_POST['elec_old'];
    $elec_new = $_POST['elec_new'];
    $water_old = $_POST['water_old'];
    $water_new = $_POST['water_new'];

    // 4. คำนวณราคา (Backend Calculation)
    $elec_price = ($elec_new - $elec_old) * 7;
    $water_price = ($water_new - $water_old) * 17;
    $total_price = $price_room + $elec_price + $water_price;

    // 5. บันทึกลงตาราง bills
    $sql = "INSERT INTO bills (user_id, room_id, month, price_room, water_unit_old, water_unit_new, water_price, elec_unit_old, elec_unit_new, elec_price, total_price, status) 
            VALUES ('$user_id', '$room_id', '$month', '$price_room', '$water_old', '$water_new', '$water_price', '$elec_old', '$elec_new', '$elec_price', '$total_price', 'unpaid')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('ออกบิลเรียบร้อยแล้ว!'); window.location='manage_bills.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>