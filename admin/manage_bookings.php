<?php
session_start();
require_once '../connect.php';

// 1. เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. จัดการ Action (เมื่อแอดมินกดปุ่ม อนุมัติ หรือ ยกเลิก)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $action = $_GET['action'];
    
    // หา room_id ของการจองนี้ก่อน (เผื่อต้องคืนสถานะห้อง)
    $sql_find_room = "SELECT room_id FROM bookings WHERE id = '$booking_id'";
    $res_find = $conn->query($sql_find_room);
    $room_data = $res_find->fetch_assoc();
    $room_id = $room_data['room_id'];

    if ($action == 'approve') {
        // อนุมัติ: อัปเดตสถานะ booking เป็น confirmed
        $sql = "UPDATE bookings SET status = 'confirmed' WHERE id = '$booking_id'";
        if($conn->query($sql)){
            echo "<script>alert('อนุมัติการจองเรียบร้อย'); window.location='manage_bookings.php';</script>";
        }
    } 
    elseif ($action == 'reject') {
        // ยกเลิก: 
        // 1. อัปเดต booking เป็น cancelled
        $sql1 = "UPDATE bookings SET status = 'cancelled' WHERE id = '$booking_id'";
        $conn->query($sql1);
        
        // 2. คืนสถานะห้องเป็น available (ว่าง)
        $sql2 = "UPDATE rooms SET status = 'available' WHERE id = '$room_id'";
        $conn->query($sql2);
        
        echo "<script>alert('ปฏิเสธการจอง และคืนสถานะห้องว่างเรียบร้อย'); window.location='manage_bookings.php';</script>";
    }
}

// 3. ดึงข้อมูลการจองทั้งหมด (JOIN ตาราง users และ rooms เพื่อเอาชื่อคนจองกับชื่อห้อง)
$sql = "SELECT b.*, u.firstname, u.lastname, u.phone, r.room_number, r.price 
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        ORDER BY b.booking_date DESC"; // เรียงจากล่าสุดไปเก่าสุด

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการจองห้องพัก - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style> body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>

    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-primary fw-bold">จัดการการจอง</span>
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
                        <h4 class="fw-bold mb-4">รายการจองห้องพักล่าสุด</h4>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>ผู้จอง</th>
                                        <th>ห้อง</th>
                                        <th>วันที่แจ้งเข้า</th>
                                        <th>หลักฐานโอน</th>
                                        <th>สถานะ</th>
                                        <th width="20%">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $row['id']; ?></td>
                                                <td>
                                                    <div class="fw-bold"><?php echo $row['firstname'] . ' ' . $row['lastname']; ?></div>
                                                    <small class="text-muted"><i class="bi bi-telephone"></i> <?php echo $row['phone']; ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $row['room_number']; ?></span>
                                                    <div class="small text-muted">฿<?php echo number_format($row['price']); ?></div>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($row['move_in_date'])); ?></td>
                                                
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#slipModal"
                                                            onclick="showSlip('../uploads/slips/<?php echo $row['slip_image']; ?>')">
                                                        <i class="bi bi-image"></i> ดูสลิป
                                                    </button>
                                                </td>

                                                <td>
                                                    <?php 
                                                        if($row['status'] == 'pending') echo '<span class="badge bg-warning text-dark">รอตรวจสอบ</span>';
                                                        else if($row['status'] == 'confirmed') echo '<span class="badge bg-success">อนุมัติแล้ว</span>';
                                                        else echo '<span class="badge bg-secondary">ยกเลิก</span>';
                                                    ?>
                                                </td>

                                                <td>
                                                    <?php if($row['status'] == 'pending'): ?>
                                                        <a href="manage_bookings.php?action=approve&id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-success btn-sm rounded-pill" onclick="return confirm('ยืนยันอนุมัติการจองนี้?');">
                                                            <i class="bi bi-check-lg"></i> อนุมัติ
                                                        </a>
                                                        <a href="manage_bookings.php?action=reject&id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-outline-danger btn-sm rounded-pill" onclick="return confirm('ต้องการปฏิเสธการจองนี้? ห้องจะกลับมาว่างทันที');">
                                                            <i class="bi bi-x-lg"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">- ดำเนินการแล้ว -</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">ไม่มีรายการจองเข้ามา</td>
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

    <div class="modal fade" id="slipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">หลักฐานการโอนเงิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <img id="slipImage" src="" class="img-fluid rounded shadow-sm" alt="Slip">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ฟังก์ชันเอารูปไปใส่ใน Modal
        function showSlip(imagePath) {
            document.getElementById('slipImage').src = imagePath;
        }
    </script>
</body>
</html>