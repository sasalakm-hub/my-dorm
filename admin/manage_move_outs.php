<?php
session_start();
require_once '../connect.php';

// เช็ค Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit(); }

// กดเปลี่ยนสถานะ (อนุมัติ / ปฏิเสธ)
// ... (โค้ดเดิมส่วนบน) ...
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    $conn->query("UPDATE move_out_requests SET status = '$status' WHERE id = '$id'");
    echo "<script>window.location='manage_move_outs.php';</script>";
}

// ⭐⭐ [จุดที่ 1] เพิ่ม Logic: Checkout (ย้ายออกเสร็จสิ้น + คืนห้องว่าง) ⭐⭐
if (isset($_GET['action']) && $_GET['action'] == 'checkout' && isset($_GET['req_id'])) {
    $req_id = $_GET['req_id'];
    $room_id = $_GET['room_id'];
    
    // 1. อัปเดตสถานะคำร้องเป็น 'completed'
    $conn->query("UPDATE move_out_requests SET status = 'completed' WHERE id = '$req_id'");
    
    // 2. คืนสถานะห้องเป็น 'available' (ว่าง)
    $conn->query("UPDATE rooms SET status = 'available' WHERE id = '$room_id'");
    
    // 3. ⭐⭐ [เพิ่มบรรทัดนี้] ตัดจบสัญญาเช่า (เปลี่ยนสถานะ Booking เป็น checked_out)
    $conn->query("UPDATE bookings SET status = 'checked_out' WHERE room_id = '$room_id' AND status = 'confirmed'");
    
    echo "<script>alert('ดำเนินการคืนห้องเสร็จสิ้น! ตัดสัญญาเช่าเรียบร้อยแล้ว'); window.location='manage_move_outs.php';</script>";
}
// ... (ส่วน Query SQL เหมือนเดิม) ...

// ดึงข้อมูลแจ้งย้ายออก
$sql = "SELECT m.*, u.firstname, u.lastname, u.phone, r.room_number 
        FROM move_out_requests m
        JOIN users u ON m.user_id = u.id
        JOIN rooms r ON m.room_id = r.id
        ORDER BY m.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการแจ้งย้ายออก - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>
    
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-primary fw-bold">รายการแจ้งย้ายออก</span>
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
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>วันที่แจ้ง</th>
                                        <th>ห้อง</th>
                                        <th>ผู้เช่า</th>
                                        <th>วันที่ขอย้าย</th>
                                        <th>ข้อมูลรับเงินคืน</th>

                                        <th>เหตุผล</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                               <tbody>
                                     <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                      <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                      <td><span class="badge bg-danger"><?php echo $row['room_number']; ?></span></td>
                                      <td>
                                        <?php echo $row['firstname'].' '.$row['lastname']; ?>
                                       <div class="small text-muted"><i class="bi bi-telephone"></i> <?php echo $row['phone']; ?></div>
                                     </td>
                                      <td class="fw-bold text-danger">
                                        <?php echo date('d/m/Y', strtotime($row['move_out_date'])); ?>
                                     </td>
        
                                    <td>
                                     <?php if(!empty($row['bank_account'])): ?>
                                       <small class="fw-bold text-primary"><?php echo $row['bank_name']; ?></small><br>
                                       <small><?php echo $row['bank_account']; ?></small><br>
                                       <small class="text-muted">(<?php echo $row['bank_owner']; ?>)</small>
                                      <?php else: ?>
                                          <span class="text-muted small">-</span>
                                      <?php endif; ?>
                                     </td>

                                     <td><?php echo $row['reason']; ?></td>
                                      <td>
                                 <?php 
                                    if($row['status']=='pending') echo '<span class="badge bg-warning text-dark">รออนุมัติ</span>';
                                    elseif($row['status']=='approved') echo '<span class="badge bg-info text-dark">รับเรื่องแล้ว</span>'; // เปลี่ยนสีนิดหน่อย
                                    elseif($row['status']=='completed') echo '<span class="badge bg-success">ย้ายออกสำเร็จ</span>';
                                    elseif($row['status']=='rejected') echo '<span class="badge bg-secondary">ยกเลิก</span>';
                                  ?>
                             </td>
        
                              <td>
                                <?php if($row['status'] == 'pending'): ?>
                                    <a href="?id=<?php echo $row['id']; ?>&status=approved" class="btn btn-sm btn-outline-primary rounded-pill mb-1" onclick="return confirm('ยืนยันรับเรื่อง?');">
                                        รับเรื่อง
                                    </a>
                                    <a href="?id=<?php echo $row['id']; ?>&status=rejected" class="btn btn-sm btn-outline-secondary rounded-pill">
                                        ปฏิเสธ
                                    </a>

                                <?php elseif($row['status'] == 'approved'): ?>
                                    <a href="?action=checkout&req_id=<?php echo $row['id']; ?>&room_id=<?php echo $row['room_id']; ?>" 
                                    class="btn btn-sm btn-danger rounded-pill shadow-sm"
                                    onclick="return confirm('ยืนยันการย้ายออก? \n- ห้องจะกลับมาสถานะ ว่าง\n- กรุณาตรวจสอบว่าคืนเงินมัดจำแล้ว');">
                                    <i class="bi bi-box-arrow-right"></i> ย้ายออก/คืนห้อง
                                    </a>

                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                                    </td>
                            </tr>
                            <?php endwhile; ?>
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