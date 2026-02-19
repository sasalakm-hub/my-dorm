<?php
session_start();
require_once '../connect.php';

// 1. เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. รับ ID ห้องที่จะแก้ไข
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM rooms WHERE id = '$id'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    // ถ้าหาไม่เจอให้เด้งกลับ
    if (!$row) {
        header("Location: manage_rooms.php");
        exit();
    }
} else {
    header("Location: manage_rooms.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลห้องพัก - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-warning bg-opacity-10 text-dark p-4 border-0 rounded-top-4">
                        <h4 class="mb-0 fw-bold">✏️ แก้ไขข้อมูลห้องพัก: <?php echo $row['room_number']; ?></h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <form action="room_update.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="old_image" value="<?php echo $row['image']; ?>">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">เลขห้อง</label>
                                    <input type="text" name="room_number" class="form-control" value="<?php echo $row['room_number']; ?>" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">ชื่อห้องพัก</label>
                                    <input type="text" name="room_name" class="form-control" value="<?php echo $row['room_name']; ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ราคาต่อเดือน (บาท)</label>
                                    <input type="number" name="price" class="form-control" value="<?php echo str_replace(',', '', $row['price']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ขนาดห้อง</label>
                                    <input type="text" name="room_size" class="form-control" value="<?php echo $row['room_size']; ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ประเภท</label>
                                    <select name="room_type" class="form-select">
                                        <option value="แอร์" <?php if($row['room_type'] == 'แอร์') echo 'selected'; ?>>แอร์</option>
                                        <option value="พัดลม" <?php if($row['room_type'] == 'พัดลม') echo 'selected'; ?>>พัดลม</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">สถานะห้อง</label>
                                    <select name="status" class="form-select">
                                        <option value="available" <?php if($row['status'] == 'available') echo 'selected'; ?>>🟢 ว่าง (Available)</option>
                                        <option value="busy" <?php if($row['status'] == 'busy') echo 'selected'; ?>>🔴 ไม่ว่าง (Busy)</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">รายละเอียดเพิ่มเติม</label>
                                    <textarea name="description" class="form-control" rows="4"><?php echo $row['description']; ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">รูปภาพห้องพัก</label>
                                    <div class="mb-2">
                                        <img src="../<?php echo $row['image']; ?>" class="rounded shadow-sm" style="height: 150px; object-fit: cover;" 
                                             onerror="this.src='https://via.placeholder.com/150?text=No+Image'">
                                    </div>
                                    <input type="file" name="room_image" class="form-control" accept="image/*">
                                    <div class="form-text">หากไม่ต้องการเปลี่ยนรูปภาพ ให้เว้นว่างไว้</div>
                                </div>
                            </div>
                            
                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="manage_rooms.php" class="btn btn-light rounded-pill px-4">ยกเลิก</a>
                                <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">บันทึกการแก้ไข</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>