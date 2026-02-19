<?php
session_start();
require_once '../connect.php';

// 1. เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. ระบบลบผู้ใช้งาน (เผื่อแอดมินต้องการลบพวก Spam)
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    
    // ป้องกันไม่ให้ลบตัวเอง
    if($del_id == $_SESSION['user_id']){
        echo "<script>alert('ไม่สามารถลบบัญชีตัวเองได้'); window.location='manage_users.php';</script>";
        exit();
    }

    // ลบข้อมูล
    $sql_del = "DELETE FROM users WHERE id = '$del_id'";
    if ($conn->query($sql_del)) {
        echo "<script>alert('ลบผู้ใช้งานเรียบร้อย'); window.location='manage_users.php';</script>";
    }
}

// 3. ดึงรายชื่อผู้ใช้ทั้งหมด + ข้อมูลห้องพักปัจจุบัน (ถ้ามี)
// ใช้ LEFT JOIN เพราะเราอยากเห็นรายชื่อทุกคน แม้เขาจะยังไม่ได้จองห้องก็ตาม
$sql = "SELECT u.*, r.room_number, r.price 
        FROM users u
        LEFT JOIN bookings b ON u.id = b.user_id AND b.status = 'confirmed'
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE u.role != 'admin' 
        ORDER BY r.room_number DESC, u.id DESC"; 
        // เรียงตามห้องก่อน (คนมีห้องขึ้นก่อน) แล้วค่อยเรียงตาม ID ใหม่สุด

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายชื่อผู้เช่า - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>

    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-primary fw-bold">รายชื่อผู้เช่า / สมาชิก</span>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            <div class="col-md-3 col-lg-2 mb-4">
                <?php include 'sidebar.php'; ?>
            </div>

            <div class="col-md-9 col-lg-10">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="fw-bold mb-0">
                                <i class="bi bi-people-fill me-2"></i>สมาชิกทั้งหมด (<?php echo $result->num_rows; ?> คน)
                            </h4>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>สถานะ</th>
                                        <th>ห้องพัก</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>เบอร์โทร / อีเมล</th>
                                        <th>วันที่สมัคร</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php if($row['room_number']): ?>
                                                        <span class="badge bg-success rounded-pill px-3">
                                                            <i class="bi bi-check-circle me-1"></i> ผู้เช่าปัจจุบัน
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary rounded-pill px-3 text-opacity-75">
                                                            <i class="bi bi-person me-1"></i> สมาชิกทั่วไป
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($row['room_number']): ?>
                                                        <span class="fw-bold text-primary fs-5"><?php echo $row['room_number']; ?></span>
                                                        <div class="small text-muted">ค่าเช่า: <?php echo number_format($row['price']); ?></div>
                                                    <?php else: ?>
                                                        <span class="text-muted small">- ยังไม่เข้าพัก -</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo $row['firstname'] . ' ' . $row['lastname']; ?></div>
                                                    <div class="small text-muted">Username: <?php echo $row['username']; ?></div>
                                                </td>
                                                <td>
                                                    <div><i class="bi bi-telephone text-primary"></i> <?php echo $row['phone']; ?></div>
                                                    <div class="small text-muted"><i class="bi bi-envelope"></i> <?php echo $row['email']; ?></div>
                                                </td>
                                                <td>
                                                    <?php 
                                                        // ถ้ามี created_at ในตาราง users ให้โชว์
                                                        // ถ้าไม่มี ให้ลบส่วนนี้ออก หรือไปเพิ่ม column ใน DB
                                                        echo isset($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '-'; 
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="manage_users.php?delete_id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger rounded-pill"
                                                       onclick="return confirm('⚠️ คำเตือน: การลบผู้ใช้นี้อาจส่งผลกระทบต่อข้อมูลการจองและบิลต่างๆ\n\nยืนยันที่จะลบคุณ <?php echo $row['firstname']; ?> ?');">
                                                        <i class="bi bi-trash"></i> ลบ
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">ยังไม่มีข้อมูลสมาชิก</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>