<?php
session_start();
require_once 'connect.php';

// 1. เช็คล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. หาว่าผู้ใช้คนนี้อยู่ห้องไหน (เพื่อเอา room_id ไปบันทึก)
$sql_room = "SELECT r.id as room_id, r.room_number 
             FROM bookings b 
             JOIN rooms r ON b.room_id = r.id 
             WHERE b.user_id = '$user_id' AND b.status = 'confirmed' 
             LIMIT 1";
$res_room = $conn->query($sql_room);
$my_room = $res_room->fetch_assoc();

// ถ้ายังไม่มีห้อง ให้เด้งออกไป
if (!$my_room) {
    echo "<script>alert('คุณยังไม่มีห้องพักในระบบ ไม่สามารถแจ้งซ่อมได้'); window.location='index.php';</script>";
    exit();
}

// 3. ดึงประวัติการแจ้งซ่อมของตัวเอง
$sql_history = "SELECT * FROM maintenance_requests WHERE user_id = '$user_id' ORDER BY created_at DESC";
$res_history = $conn->query($sql_history);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แจ้งซ่อม - หอพักแสนสุข</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-primary text-white p-4 rounded-top-4">
                        <h4 class="mb-0 fw-bold"><i class="bi bi-tools me-2"></i>แจ้งซ่อม / ปัญหา</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="maintenance_save.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="room_id" value="<?php echo $my_room['room_id']; ?>">
                            
                            <div class="alert alert-primary border-0 d-flex align-items-center mb-3">
                                <i class="bi bi-door-open-fill fs-4 me-3"></i>
                                <div>
                                    <small class="text-uppercase fw-bold">แจ้งปัญหาสำหรับห้อง</small>
                                    <div class="fs-5 fw-bold"><?php echo $my_room['room_number']; ?></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">หัวข้อปัญหา</label>
                                <select name="topic" class="form-select" required>
                                    <option value="" selected disabled>-- เลือกประเภทปัญหา --</option>
                                    <option value="ไฟฟ้า/หลอดไฟ">ไฟฟ้า / หลอดไฟ</option>
                                    <option value="ประปา/น้ำรั่ว">ประปา / น้ำรั่ว / ก๊อกน้ำ</option>
                                    <option value="เครื่องใช้ไฟฟ้า">แอร์ / เครื่องทำน้ำอุ่น</option>
                                    <option value="เฟอร์นิเจอร์">เฟอร์นิเจอร์ / ประตู / หน้าต่าง</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">รายละเอียดเพิ่มเติม</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="ระบุอาการเสีย หรือรายละเอียด..." required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">รูปภาพประกอบ (ถ้ามี)</label>
                                <input type="file" name="repair_image" class="form-control" accept="image/*">
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                                <i class="bi bi-send-fill me-2"></i>ส่งเรื่องแจ้งซ่อม
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <h4 class="fw-bold mb-3 ms-2"><i class="bi bi-clock-history me-2"></i>ประวัติการแจ้งซ่อมของฉัน</h4>
                
                <?php if ($res_history->num_rows > 0): ?>
                    <?php while($row = $res_history->fetch_assoc()): ?>
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <?php if($row['image']): ?>
                                            <img src="uploads/repairs/<?php echo $row['image']; ?>" class="rounded-3" style="width: 80px; height: 80px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="width: 80px; height: 80px;">
                                                <i class="bi bi-image fs-4"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="fw-bold text-dark mb-1"><?php echo $row['topic']; ?></h5>
                                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></small>
                                        </div>
                                        <p class="text-muted small mb-2 text-truncate" style="max-width: 300px;">
                                            <?php echo $row['description']; ?>
                                        </p>
                                        
                                        <?php 
                                            if($row['status'] == 'pending') 
                                                echo '<span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-hourglass-split"></i> รอดำเนินการ</span>';
                                            elseif($row['status'] == 'in_progress') 
                                                echo '<span class="badge bg-info text-dark rounded-pill"><i class="bi bi-tools"></i> กำลังซ่อมแซม</span>';
                                            elseif($row['status'] == 'done') 
                                                echo '<span class="badge bg-success rounded-pill"><i class="bi bi-check-circle"></i> ซ่อมเสร็จแล้ว</span>';
                                        ?>

                                        <?php if (!empty($row['admin_response'])): ?>
                                            <div class="alert alert-primary bg-opacity-10 border-0 mt-3 mb-0 p-3 rounded-3">
                                                <div class="d-flex">
                                                    <i class="bi bi-chat-quote-fill text-primary me-2 fs-5"></i>
                                                    <div>
                                                        <strong class="text-primary d-block mb-1">ข้อความจากนิติ/ช่าง:</strong>
                                                        <span class="text-dark small"><?php echo $row['admin_response']; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm text-muted">
                        <i class="bi bi-tools display-1 mb-3"></i>
                        <p>ยังไม่มีรายการแจ้งซ่อม</p>
                    </div>
                <?php endif; ?>

            </div>           

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>