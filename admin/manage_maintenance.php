<?php
session_start();
require_once '../connect.php';

// เช็ค Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit(); }

// ⭐ [แก้ไขใหม่] รับค่าแบบ POST จาก Modal (อัปเดตสถานะ + ข้อความตอบกลับ)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_update'])) {
    $id = $_POST['request_id'];
    $status = $_POST['status'];
    $admin_response = $_POST['admin_response'];

    $sql = "UPDATE maintenance_requests SET status = '$status', admin_response = '$admin_response' WHERE id = '$id'";
    
    if($conn->query($sql)){
        echo "<script>alert('อัปเดตงานซ่อมเรียบร้อย'); window.location='manage_maintenance.php';</script>";
    }
}

// ดึงข้อมูลแจ้งซ่อมทั้งหมด
$sql = "SELECT m.*, r.room_number, u.firstname, u.phone 
        FROM maintenance_requests m 
        JOIN rooms r ON m.room_id = r.id 
        JOIN users u ON m.user_id = u.id 
        ORDER BY m.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการแจ้งซ่อม - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>
    
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-primary fw-bold">รายการแจ้งซ่อม</span>
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
                                        <th>ห้อง / ผู้แจ้ง</th>
                                        <th>ปัญหา</th>
                                        <th>รูปภาพ</th>
                                        <th>สถานะ / การตอบกลับ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-primary mb-1"><?php echo $row['room_number']; ?></span>
                                            <div class="small fw-bold"><?php echo $row['firstname']; ?></div>
                                            <div class="small text-muted"><i class="bi bi-telephone"></i> <?php echo $row['phone']; ?></div>
                                        </td>
                                        <td>
                                            <strong><?php echo $row['topic']; ?></strong>
                                            <p class="small text-muted mb-0"><?php echo $row['description']; ?></p>
                                        </td>
                                        <td>
                                            <?php if($row['image']): ?>
                                                <a href="../uploads/repairs/<?php echo $row['image']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">ดูรูป</a>
                                            <?php else: ?> - <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if($row['status'] == 'pending') echo '<span class="badge bg-warning text-dark">รอดำเนินการ</span>';
                                                elseif($row['status'] == 'in_progress') echo '<span class="badge bg-info text-dark">กำลังซ่อม</span>';
                                                elseif($row['status'] == 'done') echo '<span class="badge bg-success">เสร็จสิ้น</span>';
                                            ?>
                                            <?php if($row['admin_response']): ?>
                                                <div class="alert alert-light border mt-2 p-2 small mb-0 text-muted">
                                                    <i class="bi bi-chat-dots-fill text-primary"></i> 
                                                    <?php echo $row['admin_response']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill" 
                                                    onclick="openUpdateModal('<?php echo $row['id']; ?>', '<?php echo $row['status']; ?>', '<?php echo htmlspecialchars($row['admin_response'], ENT_QUOTES); ?>')">
                                                <i class="bi bi-pencil-square"></i> อัปเดต
                                            </button>
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

    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">อัปเดตงานแจ้งซ่อม</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="request_id" id="modal_request_id">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">สถานะการซ่อม</label>
                            <select name="status" id="modal_status" class="form-select">
                                <option value="pending">🟡 รอดำเนินการ</option>
                                <option value="in_progress">🔵 กำลังซ่อมแซม (รับเรื่องแล้ว)</option>
                                <option value="done">🟢 ซ่อมเสร็จสิ้น</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">ข้อความตอบกลับถึงผู้เช่า</label>
                            <textarea name="admin_response" id="modal_response" class="form-control" rows="3" placeholder="เช่น ช่างจะเข้าไปพรุ่งนี้ เวลา 10.00 น. หรือ ซ่อมเสร็จเรียบร้อยแล้วครับ"></textarea>
                            <div class="form-text">ข้อความนี้จะไปแสดงที่หน้าประวัติแจ้งซ่อมของผู้เช่า</div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" name="btn_update" class="btn btn-primary rounded-pill px-4">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ฟังก์ชัน Javascript เพื่อดึงค่าไปใส่ใน Modal
        function openUpdateModal(id, status, response) {
            document.getElementById('modal_request_id').value = id;
            document.getElementById('modal_status').value = status;
            document.getElementById('modal_response').value = response; // ใส่ข้อความเดิม (ถ้ามี)
            
            // เปิด Modal
            var myModal = new bootstrap.Modal(document.getElementById('updateModal'));
            myModal.show();
        }
    </script>
</body>
</html>