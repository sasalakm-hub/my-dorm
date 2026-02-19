<?php
session_start();
session_destroy(); // ล้างข้อมูล Session ทั้งหมด (เหมือนระเบิดทิ้ง)
header("Location: index.php"); // ดีดกลับไปหน้าแรก
exit();
?>