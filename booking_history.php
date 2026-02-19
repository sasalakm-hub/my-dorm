<?php
session_start();
require_once 'connect.php';

// เช็คล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลการจองของผู้ใช้คนนี้ (เชื่อมตาราง rooms เพื่อเอารูปกับชื่อห้องมาโชว์ด้วย)
$sql = "SELECT b.*, r.room_number, r.room_name, r.price, r.image 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        WHERE b.user_id = '$user_id' 
        ORDER BY b.booking_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการจอง - หอพักแสนสุข</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <h2 class="fw-bold mb-4"><i class="bi bi-clock-history me-2"></i>ประวัติการจองห้องพัก</h2>

        <div class="row">
            <div class="col-md-12">
                
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        
                        <div class="card shadow-sm border-0 rounded-4 mb-3 overflow-hidden">
                            <div class="card-body p-0">
                                <div class="row g-0">
                                    <div class="col-md-3">
                                        <img src="<?php echo $row['image']; ?>" class="img-fluid h-100 w-100" style="object-fit: cover; min-height: 200px;" alt="รูปห้องพัก">
                                    </div>
                                    
                                    <div class="col-md-9 p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h4 class="fw-bold text-primary mb-1">
                                                    <?php echo $row['room_number'] . " : " . $row['room_name']; ?>
                                                </h4>
                                                <p class="text-muted small mb-0">
                                                    วันที่ทำรายการ: <?php echo date('d/m/Y H:i', strtotime($row['booking_date'])); ?>
                                                </p>
                                            </div>
                                            
                                            <?php 
                                                $status = $row['status'];
                                                if($status == 'pending'){
                                                    echo '<span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="bi bi-hourglass-split me-1"></i> รอตรวจสอบการชำระเงิน</span>';
                                                } elseif($status == 'confirmed'){
                                                    echo '<span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i> จองสำเร็จ / อนุมัติแล้ว</span>';
                                                } elseif($status == 'cancelled'){
                                                    echo '<span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle-fill me-1"></i> ยกเลิก</span>';
                                                }
                                                // ⭐⭐ เพิ่มสถานะ checked_out ตรงนี้ครับ
                                                elseif($status == 'checked_out'){
                                                    echo '<span class="badge bg-secondary rounded-pill px-3 py-2"><i class="bi bi-house-dash-fill me-1"></i> ย้ายออก / คืนห้องเสร็จสิ้น</span>';
                                                }
                                            ?>
                                        </div>

                                        <hr>

                                        <div class="row mt-3">
                                            <div class="col-md-4 mb-2">
                                                <span class="text-muted small d-block">วันที่แจ้งเข้าพัก (Check-in)</span>
                                                <span class="fw-bold fs-5 text-dark">
                                                    <i class="bi bi-calendar-event me-2"></i>
                                                    <?php echo date('d/m/Y', strtotime($row['move_in_date'])); ?>
                                                </span>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <span class="text-muted small d-block">ค่าเช่ารายเดือน</span>
                                                <span class="fw-bold">฿<?php echo number_format($row['price']); ?></span>
                                            </div>
                                            <div class="col-md-4 mb-2 text-md-end">
                                                <a href="uploads/slips/<?php echo $row['slip_image']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill">
                                                    <i class="bi bi-receipt me-1"></i> ดูหลักฐานการโอน
                                                </a>
                                            </div>
                                        </div>

                                        <?php if($status == 'confirmed'): ?>
                                            <div class="alert alert-success mt-3 mb-0 py-2 small border-0">
                                                <i class="bi bi-info-circle me-2"></i> 
                                                <strong>อนุมัติแล้ว!</strong> คุณสามารถย้ายเข้าพักได้ตามวันที่ระบุ กรุณาติดต่อนิติบุคคลเพื่อรับกุญแจ
                                            </div>
                                        <?php elseif($status == 'pending'): ?>
                                            <div class="alert alert-warning mt-3 mb-0 py-2 small border-0 bg-warning bg-opacity-10 text-warning-emphasis">
                                                <i class="bi bi-info-circle me-2"></i> 
                                                เจ้าหน้าที่กำลังตรวจสอบหลักฐานการโอนเงิน (ใช้เวลา 1-24 ชม.)
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                        <i class="bi bi-clipboard-x text-muted display-1"></i>
                        <h4 class="mt-3 text-muted">คุณยังไม่มีประวัติการจองห้องพัก</h4>
                        <a href="rooms.php" class="btn btn-primary rounded-pill mt-3 px-4">ค้นหาห้องพัก</a>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>