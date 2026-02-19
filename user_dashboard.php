<?php
session_start();
require_once 'connect.php';

// 1. เช็คล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. ดึงข้อมูลการจองที่ "อนุมัติแล้ว" (Confirmed) ล่าสุด
// เพื่อดูว่าเขาอยู่ห้องไหน
$sql = "SELECT b.*, r.room_number, r.room_name, r.price, u.firstname, u.lastname 
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN users u ON b.user_id = u.id
        WHERE b.user_id = '$user_id' AND b.status = 'confirmed'
        ORDER BY b.booking_date DESC LIMIT 1";

$result = $conn->query($sql);
$booking_data = $result->fetch_assoc();

// ถ้ายังไม่มีห้องที่อนุมัติ ให้เด้งกลับไปหน้า Index หรือ Booking History
if (!$booking_data) {
    echo "<script>alert('คุณยังไม่มีสถานะเข้าพักที่ได้รับการอนุมัติ'); window.location='booking_history.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Dashboard ผู้พักอาศัย - หอพักแสนสุข</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .quick-action-card { transition: transform 0.2s; cursor: pointer; }
        .quick-action-card:hover { transform: translateY(-5px); }
        .bg-gradient-primary { background: linear-gradient(45deg, #0d6efd, #0dcaf0); }
    </style>
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="bg-white shadow-sm border-bottom mb-4">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-1">
                        👋 สวัสดี, คุณ<?php echo $booking_data['firstname']; ?> 
                        <span class="text-primary">(ห้อง <?php echo $booking_data['room_number']; ?>)</span>
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="bi bi-file-earmark-check-fill text-success me-1"></i> สถานะสัญญา: 
                        <span class="text-success fw-bold">ปกติ</span> 
                        | เข้าอยู่เมื่อ: <?php echo date('d/m/Y', strtotime($booking_data['move_in_date'])); ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <button class="btn btn-outline-danger rounded-pill btn-sm">
                        <i class="bi bi-exclamation-triangle me-1"></i> แจ้งเหตุฉุกเฉิน
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        
        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <?php
                // ดึงบิลล่าสุดที่ยังไม่ได้จ่าย (status = unpaid) หรือ รอตรวจสอบ (pending)
                $sql_bill = "SELECT * FROM bills WHERE user_id = '$user_id' AND status != 'paid' ORDER BY id DESC LIMIT 1";
                $res_bill = $conn->query($sql_bill);
                $bill = $res_bill->fetch_assoc();
                ?>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <?php if ($bill): ?>
                        
                        <?php if($bill['status'] == 'unpaid'): ?>
                            <div class="card-header bg-danger text-white border-0 py-3">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-receipt me-2"></i>บิลค่าเช่ารอชำระ</h5>
                            </div>
                            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap">
                                <div>
                                    <h6 class="text-muted mb-1">ประจำเดือน: <strong><?php echo date('F Y', strtotime($bill['month'])); ?></strong></h6>
                                    <h2 class="fw-bold text-danger mb-0">฿<?php echo number_format($bill['total_price'], 2); ?></h2>
                                    <small class="text-danger">* กรุณาชำระภายในวันที่ 5 ของเดือน</small>
                                </div>
                                <a href="bill_payment.php?id=<?php echo $bill['id']; ?>" class="btn btn-danger rounded-pill px-4 mt-3 mt-sm-0 shadow-sm">
                                    <i class="bi bi-qr-code-scan me-2"></i>แจ้งชำระเงิน
                                </a>
                            </div>

                        <?php elseif($bill['status'] == 'pending'): ?>
                            <div class="card-header bg-warning text-dark border-0 py-3">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-hourglass-split me-2"></i>รอตรวจสอบยอดเงิน</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="display-4 me-3 text-warning"><i class="bi bi-clock-history"></i></div>
                                    <div>
                                        <h5 class="fw-bold">แจ้งชำระเรียบร้อยแล้ว</h5>
                                        <p class="text-muted mb-0">เจ้าหน้าที่กำลังตรวจสอบสลิปของคุณ (ใช้เวลา 1-24 ชม.)</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="card-header bg-success text-white border-0 py-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-check-circle-fill me-2"></i>สถานะการชำระเงิน</h5>
                        </div>
                        <div class="card-body p-4 text-center">
                            <div class="py-2">
                                <i class="bi bi-emoji-smile-fill text-success display-4 mb-3"></i>
                                <h5 class="fw-bold text-success">ไม่มียอดค้างชำระ</h5>
                                <p class="text-muted mb-0">ขอบคุณที่คุณชำระค่าเช่าตรงเวลาครับ</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

                            <?php
                    // ดึงจำนวนพัสดุที่รอรับ (status = waiting)
                    $sql_parcel = "SELECT COUNT(*) as count FROM parcels WHERE user_id = '$user_id' AND status = 'waiting'";
                    $res_parcel = $conn->query($sql_parcel);
                    $parcel_count = $res_parcel->fetch_assoc()['count'];
                ?>

                <div class="col-md-4">
                    <?php if($parcel_count > 0): ?>
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-warning bg-opacity-10">
                            <div class="card-body p-4 text-center">
                                <div class="bg-white rounded-circle shadow-sm d-inline-flex p-3 mb-3 text-warning">
                                    <i class="bi bi-box-seam-fill fs-1"></i>
                                </div>
                                <h5 class="fw-bold">มีพัสดุรอรับ <?php echo $parcel_count; ?> ชิ้น</h5>
                                <p class="small text-muted mb-3">กรุณาติดต่อรับที่ป้อม รปภ. / นิติบุคคล</p>
                                
                                <button class="btn btn-sm btn-warning rounded-pill px-3 shadow-sm text-dark fw-bold">
                                    อย่าลืมไปรับนะครับ!
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                            <div class="card-body p-4 text-center">
                                <div class="bg-light rounded-circle d-inline-flex p-3 mb-3 text-secondary">
                                    <i class="bi bi-box-seam fs-1"></i>
                                </div>
                                <h5 class="fw-bold text-muted">ไม่มีพัสดุตกค้าง</h5>
                                <p class="small text-muted mb-0">ยังไม่มีพัสดุมาส่งถึงคุณในขณะนี้</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

        <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-grid-fill me-2"></i>เมนูด่วน</h5>
        <div class="row g-3 mb-5">
            <div class="col-6 col-md-3">
                <a href="maintenance.php" class="text-decoration-none text-dark">
                    <div class="card quick-action-card border-0 shadow-sm rounded-4 text-center py-4 h-100">
                        <div class="text-primary mb-2"><i class="bi bi-tools fs-2"></i></div>
                         <h6 class="fw-bold mb-0">แจ้งซ่อม</h6>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <div class="card quick-action-card border-0 shadow-sm rounded-4 text-center py-4 h-100">
                    <div class="text-info mb-2"><i class="bi bi-file-earmark-text fs-2"></i></div>
                    <h6 class="fw-bold mb-0">สัญญาเช่า</h6>
                </div>
            </div>
            
           <div class="col-6 col-md-3">
                <a href="payment_history.php" class="text-decoration-none text-dark">
                    <div class="card quick-action-card border-0 shadow-sm rounded-4 text-center py-4 h-100">
                        <div class="text-success mb-2"><i class="bi bi-clock-history fs-2"></i></div>
                        <h6 class="fw-bold mb-0">ประวัติการจ่าย</h6>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-3">
                <a href="move_out.php" class="text-decoration-none text-dark">
                    <div class="card quick-action-card border-0 shadow-sm rounded-4 text-center py-4 h-100 text-danger">
                        <div class="mb-2"><i class="bi bi-box-arrow-right fs-2"></i></div>
                        <h6 class="fw-bold mb-0">แจ้งย้ายออก</h6>
                    </div>
                </a>
            </div>
        </div>

        <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-megaphone-fill me-2"></i>ข่าวสารจากหอพัก</h5>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="list-group list-group-flush rounded-4">
                <div class="list-group-item p-4">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 fw-bold text-primary">💧 แจ้งล้างแท็งก์น้ำประจำปี</h5>
                        <small class="text-muted">3 วันที่แล้ว</small>
                    </div>
                    <p class="mb-1 text-muted">จะมีการดำเนินการล้างแท็งก์น้ำในวันที่ 25 นี้ ช่วงเวลา 10.00 - 12.00 น. ขออภัยในความไม่สะดวก</p>
                </div>
                <div class="list-group-item p-4">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 fw-bold text-primary">👮‍♂️ เพิ่มมาตรการรักษาความปลอดภัย</h5>
                        <small class="text-muted">1 สัปดาห์ที่แล้ว</small>
                    </div>
                    <p class="mb-1 text-muted">ทางหอพักได้ติดตั้งกล้องวงจรปิดเพิ่มเติมบริเวณโรงจอดรถมอเตอร์ไซค์ เพื่อความปลอดภัยของทรัพย์สิน</p>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>