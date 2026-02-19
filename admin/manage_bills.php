<?php
session_start();
require_once '../connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit(); }

// ... (ส่วน PHP ดึงข้อมูล tenants เหมือนเดิม ไม่ต้องแก้) ...
$sql_tenants = "SELECT u.id as user_id, u.firstname, u.lastname, r.id as room_id, r.room_number, r.price 
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN rooms r ON b.room_id = r.id
                WHERE b.status = 'confirmed'";
$res_tenants = $conn->query($sql_tenants);

// ดึงรายการบิลทั้งหมด
$sql_bills = "SELECT b.*, u.firstname, r.room_number 
              FROM bills b
              JOIN users u ON b.user_id = u.id
              JOIN rooms r ON b.room_id = r.id
              ORDER BY b.id DESC";
$res_bills = $conn->query($sql_bills);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการบิลค่าเช่า - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>
    
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-primary fw-bold">จัดการบิลค่าเช่า / ตรวจสลิป</span>
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
                            <h4 class="fw-bold mb-0">รายการบิลทั้งหมด</h4>
                            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createBillModal">
                                <i class="bi bi-plus-lg me-2"></i>ออกบิลใหม่
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>รอบเดือน</th>
                                        <th>ห้อง</th>
                                        <th>ยอดรวม</th>
                                        <th>สถานะ</th>
                                        <th>หลักฐานการโอน</th> <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $res_bills->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M Y', strtotime($row['month'])); ?></td>
                                        <td><span class="badge bg-primary"><?php echo $row['room_number']; ?></span></td>
                                        <td class="fw-bold text-success">฿<?php echo number_format($row['total_price']); ?></td>
                                        <td>
                                            <?php 
                                                if($row['status']=='unpaid') echo '<span class="badge bg-danger">ค้างชำระ</span>';
                                                elseif($row['status']=='pending') echo '<span class="badge bg-warning text-dark">รอตรวจสอบ</span>';
                                                elseif($row['status']=='paid') echo '<span class="badge bg-success">ชำระแล้ว</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php if($row['slip_image']): ?>
                                                <button class="btn btn-sm btn-outline-primary rounded-pill" 
                                                        onclick="viewSlip('../uploads/bill_slips/<?php echo $row['slip_image']; ?>', '<?php echo $row['room_number']; ?>', '<?php echo $row['total_price']; ?>')">
                                                    <i class="bi bi-eye"></i> ดูสลิป
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['status'] == 'pending'): ?>
                                                <a href="bill_approve.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-success rounded-pill" 
                                                   onclick="return confirm('ตรวจสอบยอดเงินถูกต้องแล้ว ยืนยันการอนุมัติ?');">
                                                   <i class="bi bi-check-lg"></i> อนุมัติ
                                                </a>
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

    <div class="modal fade" id="slipModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>ตรวจสอบสลิปโอนเงิน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <div class="mb-2">
                        <span class="badge bg-secondary mb-2" id="modal_room"></span>
                        <h4 class="text-success fw-bold" id="modal_price"></h4>
                    </div>
                    <img id="slip_preview" src="" class="img-fluid rounded shadow-sm border" style="max-height: 500px;">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createBillModal" tabindex="-1">
         <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">📝 ออกบิลค่าเช่า / จดมิเตอร์</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="bill_save.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">เลือกห้องพัก</label>
                                <select name="booking_ref" id="select_room" class="form-select" required onchange="calculateTotal()">
                                    <option value="" selected disabled>-- เลือกห้อง --</option>
                                    <?php 
                                        // Reset pointer ของ tenants เพื่อวนลูปใหม่ (เพราะข้างบนอาจจะใช้ไปแล้ว)
                                        $res_tenants->data_seek(0);
                                        while($t = $res_tenants->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $t['user_id'].','.$t['room_id']; ?>" data-price="<?php echo $t['price']; ?>">
                                            ห้อง <?php echo $t['room_number']; ?> (คุณ<?php echo $t['firstname']; ?>) - ค่าห้อง <?php echo number_format($t['price']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ประจำเดือน</label>
                                <input type="month" name="month" class="form-control" required>
                            </div>

                            <div class="col-12"><hr class="my-1"></div>
                            <h6 class="fw-bold text-warning">ค่าไฟฟ้า (หน่วยละ 7 บาท)</h6>
                            <div class="col-md-6"><label>เลขมิเตอร์ครั้งก่อน</label><input type="number" name="elec_old" id="elec_old" class="form-control" value="0" oninput="calculateTotal()" required></div>
                            <div class="col-md-6"><label>เลขมิเตอร์ปัจจุบัน</label><input type="number" name="elec_new" id="elec_new" class="form-control" value="0" oninput="calculateTotal()" required></div>

                            <div class="col-12"><hr class="my-1"></div>
                            <h6 class="fw-bold text-info">ค่าน้ำประปา (หน่วยละ 17 บาท)</h6>
                            <div class="col-md-6"><label>เลขมิเตอร์ครั้งก่อน</label><input type="number" name="water_old" id="water_old" class="form-control" value="0" oninput="calculateTotal()" required></div>
                            <div class="col-md-6"><label>เลขมิเตอร์ปัจจุบัน</label><input type="number" name="water_new" id="water_new" class="form-control" value="0" oninput="calculateTotal()" required></div>

                            <div class="col-12 mt-4">
                                <div class="alert alert-success text-center">
                                    <h4 class="mb-0">ยอดรวมสุทธิ: <span id="show_total" class="fw-bold">0.00</span> บาท</h4>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">ยืนยันออกบิล</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ฟังก์ชันเปิด Modal ดูสลิป
        function viewSlip(imgSrc, room, price) {
            document.getElementById('slip_preview').src = imgSrc;
            document.getElementById('modal_room').innerText = 'ห้อง ' + room;
            document.getElementById('modal_price').innerText = 'ยอดเงิน ' + parseFloat(price).toLocaleString() + ' บาท';
            
            var myModal = new bootstrap.Modal(document.getElementById('slipModal'));
            myModal.show();
        }

        // ฟังก์ชันคำนวณบิล (เหมือนเดิม)
        function calculateTotal() {
            let select = document.getElementById('select_room');
            let roomPrice = 0;
            if(select.selectedIndex > 0) {
                roomPrice = parseFloat(select.options[select.selectedIndex].getAttribute('data-price'));
            }
            let elecTotal = (parseFloat(document.getElementById('elec_new').value||0) - parseFloat(document.getElementById('elec_old').value||0)) * 7;
            let waterTotal = (parseFloat(document.getElementById('water_new').value||0) - parseFloat(document.getElementById('water_old').value||0)) * 17;
            if(elecTotal<0) elecTotal=0; if(waterTotal<0) waterTotal=0;
            
            document.getElementById('show_total').innerText = (roomPrice + elecTotal + waterTotal).toLocaleString(undefined, {minimumFractionDigits:2});
        }
    </script>
</body>
</html>