<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// หาห้องปัจจุบัน
$sql = "SELECT b.room_id, r.room_number FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.user_id = '$user_id' AND b.status = 'confirmed' LIMIT 1";
$res = $conn->query($sql);
$my_room = $res->fetch_assoc();

if (!$my_room) { echo "<script>alert('ไม่พบห้องพักของคุณ'); window.location='index.php';</script>"; exit(); }

// เช็คว่าเคยแจ้งไปแล้วหรือยัง (ที่ยังไม่เสร็จสิ้น)
$sql_check = "SELECT * FROM move_out_requests WHERE user_id = '$user_id' AND status = 'pending'";
$res_check = $conn->query($sql_check);
$pending_request = $res_check->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แจ้งย้ายออก - หอพักแสนสุข</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-danger text-white p-4 rounded-top-4">
                        <h4 class="mb-0 fw-bold"><i class="bi bi-box-arrow-right me-2"></i>แจ้งความประสงค์ย้ายออก</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if($pending_request): ?>
                            <div class="alert alert-warning text-center">
                                <i class="bi bi-hourglass-split display-4 mb-2"></i>
                                <h5>คุณได้ส่งเรื่องแจ้งย้ายออกไปแล้ว</h5>
                                <p>วันที่ต้องการย้ายออก: <strong><?php echo date('d/m/Y', strtotime($pending_request['move_out_date'])); ?></strong></p>
                                <p class="mb-0">กรุณารอเจ้าน้าที่ติดต่อกลับเพื่อยืนยันวันเวลาตรวจห้อง</p>
                            </div>
                            <div class="text-center">
                                <a href="user_dashboard.php" class="btn btn-outline-secondary rounded-pill">กลับหน้าหลัก</a>
                            </div>

                        <?php else: ?>
                           <form action="move_out_save.php" method="POST">
                                 <input type="hidden" name="room_id" value="<?php echo $my_room['room_id']; ?>">
    
                                  <div class="mb-3 text-center">
                                     <h1 class="display-4 fw-bold text-primary"><?php echo $my_room['room_number']; ?></h1>
                                     <p class="text-muted">ห้องพักปัจจุบันของคุณ</p>
                                   </div>

                                 <div class="mb-3">
                                     <label class="form-label fw-bold">วันที่ต้องการย้ายออก</label>
                                      <input type="date" name="move_out_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                     <div class="form-text text-danger">* กรุณาแจ้งล่วงหน้าอย่างน้อย 30 วัน</div>
                                 </div>

                                 <div class="mb-3">
                                    <label class="form-label fw-bold">เหตุผลการย้ายออก</label>
                                  <textarea name="reason" class="form-control" rows="2"></textarea>
                                 </div>

                         <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                           <h6 class="fw-bold text-primary"><i class="bi bi-bank me-2"></i>ข้อมูลบัญชีรับเงินประกันคืน</h6>
                          <div class="row g-2">
                                <div class="col-md-6">
                                  <label class="small text-muted">ธนาคาร</label>
                                  <select name="bank_name" class="form-select" required>
                                     <option value="กสิกรไทย">กสิกรไทย</option>
                                         <option value="ไทยพาณิชย์">ไทยพาณิชย์</option>
                                         <option value="กรุงเทพ">กรุงเทพ</option>
                                         <option value="กรุงไทย">กรุงไทย</option>
                                         <option value="กรุงศรี">กรุงศรี</option>
                                         <option value="ออมสิน">ออมสิน</option>
                                         <option value="อื่นๆ">อื่นๆ</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="small text-muted">เลขที่บัญชี</label>
                <input type="text" name="bank_account" class="form-control" placeholder="ไม่ต้องมีขีด" required>
            </div>
            <div class="col-12">
                <label class="small text-muted">ชื่อบัญชี (ต้องตรงกับชื่อผู้เช่า)</label>
                <input type="text" name="bank_owner" class="form-control" required>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-danger w-100 rounded-pill py-3 fw-bold" onclick="return confirm('ยืนยันการแจ้งย้ายออก?');">
        ส่งเรื่องแจ้งย้ายออก
    </button>
    <a href="user_dashboard.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted">ยกเลิก</a>
</form>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>