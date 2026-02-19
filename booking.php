<?php
session_start();
require_once 'connect.php';

// 1. เช็คว่าล็อกอินไหม
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. รับค่า room_id
if (!isset($_GET['room_id'])) {
    header("Location: rooms.php");
    exit();
}

$room_id = $_GET['room_id'];

// 3. ดึงข้อมูลห้อง
$sql = "SELECT * FROM rooms WHERE id = '$room_id'";
$result = $conn->query($sql);
$room = $result->fetch_assoc();

if (!$room || $room['status'] != 'available') {
    echo "<script>alert('ห้องนี้ไม่ว่าง หรือไม่พบข้อมูล'); window.location='rooms.php';</script>";
    exit();
}

// --- ⭐ ส่วนคำนวณเงิน (เพิ่มใหม่ตรงนี้) ---
$deposit = 2000; // ค่ามัดจำ (กำหนดตายตัว)
$room_price = $room['price']; // ค่าห้อง
$total_pay = $room_price + $deposit; // ยอดรวมที่ต้องจ่าย
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ยืนยันการจอง - หอพักแสนสุข</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-primary text-white p-4 rounded-top-4">
                        <h4 class="mb-0 fw-bold">📝 ยืนยันการจองห้องพัก</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <form action="booking_save.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                            
                            <div class="row mb-4 align-items-center">
                                <div class="col-md-4">
                                    <img src="<?php echo $room['image']; ?>" class="img-fluid rounded-3 shadow-sm w-100" style="height: 150px; object-fit: cover;">
                                </div>
                                <div class="col-md-8">
                                    <span class="badge bg-warning text-dark mb-2">รอการยืนยันการจอง</span>
                                    <h5 class="fw-bold text-primary mb-1"><?php echo $room['room_number'] . " : " . $room['room_name']; ?></h5>
                                    <p class="text-muted small mb-2"><?php echo $room['room_size']; ?> | <?php echo $room['room_type']; ?></p>
                                    
                                    <h3 class="fw-bold text-dark mb-0">ค่าเช่า: ฿<?php echo number_format($room_price); ?> / เดือน</h3>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-text me-2"></i>ข้อตกลงและสัญญาเช่า</h5>
                                <div class="p-3 bg-white border rounded-3" style="height: 200px; overflow-y: scroll;">
                                    <p class="fw-bold">ข้อกำหนดและเงื่อนไขการเข้าพัก หอพักแสนสุข</p>
                                    <ol class="small text-muted">
                                        <li><strong>การชำระเงินแรกเข้า:</strong> ผู้เช่าต้องชำระ "ค่าเช่าล่วงหน้า 1 เดือน" รวมกับ "เงินประกันความเสียหาย" ก่อนเข้าอยู่</li>
                                        <li><strong>เงินประกันความเสียหาย:</strong> จำนวน 2,000 บาท จะได้รับคืนเมื่อย้ายออกและห้องพักอยู่ในสภาพสมบูรณ์</li>
                                        <li><strong>สัญญาเช่า:</strong> ขั้นต่ำ 6 เดือน หากย้ายออกก่อนจะถูกริบเงินประกัน</li>
                                        <li><strong>กฎระเบียบ:</strong> ห้ามเลี้ยงสัตว์, ห้ามส่งเสียงดังหลัง 22.00 น., ห้ามเสพสิ่งเสพติด</li>
                                    </ol>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="agreeCheckbox" name="agree" required>
                                    <label class="form-check-label user-select-none" for="agreeCheckbox">
                                        ข้าพเจ้าได้อ่านและยอมรับเงื่อนไขในสัญญาเช่าฉบับนี้
                                    </label>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <h5 class="fw-bold mb-3"><i class="bi bi-wallet2 me-2"></i>สรุปยอดที่ต้องชำระ</h5>
                                
                                <div class="bg-light p-4 rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>ค่าเช่าห้องเดือนแรก</span>
                                        <span class="fw-bold">฿<?php echo number_format($room_price); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 text-muted">
                                        <span>ค่าเงินประกัน/มัดจำ</span>
                                        <span class="fw-bold">฿<?php echo number_format($deposit); ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold text-dark">ยอดรวมสุทธิ</span>
                                        <span class="fs-4 fw-bold text-success">฿<?php echo number_format($total_pay); ?></span>
                                    </div>
                                </div>

                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                    <div>
                                        กรุณาโอนเงินยอด <strong><?php echo number_format($total_pay); ?> บาท</strong> เพื่อยืนยันการจอง
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-white h-100 text-center">
                                            <img src="images/qr.jpg" width="300" alt="KBank" class="mb-2">
                                            <p class="mb-0 small text-muted">ธนาคารกสิกรไทย</p>
                                            <h5 class="fw-bold text-primary my-1">123-4-56789-0</h5>
                                            <p class="mb-0 small">ชื่อบัญชี: หอพักแสนสุข จำกัด</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label small fw-bold">วันที่ต้องการเข้าพัก</label>
                                            <input type="date" class="form-control" name="move_in_date" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="form-label small fw-bold">วันที่โอนเงิน</label>
                                            <input type="datetime-local" class="form-control" name="transfer_date" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label small fw-bold">หลักฐานการโอนเงิน (สลิป)</label>
                                            <input type="file" class="form-control" name="slip" accept="image/*" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm py-3">
                                    <i class="bi bi-check-circle-fill me-2"></i>ยืนยันการจอง (ชำระ ฿<?php echo number_format($total_pay); ?>)
                                </button>
                                <a href="rooms.php" class="btn btn-outline-secondary rounded-pill">ยกเลิก</a>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>