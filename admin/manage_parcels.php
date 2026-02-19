<?php
session_start();
require_once '../connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit(); }

// 1. บันทึกพัสดุใหม่ (Add)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_parcel'])) {
    $ref = explode(',', $_POST['tenant_ref']); // user_id,room_id
    $user_id = $ref[0];
    $room_id = $ref[1];
    $description = $_POST['description'];

    $sql = "INSERT INTO parcels (user_id, room_id, description, status) VALUES ('$user_id', '$room_id', '$description', 'waiting')";
    if($conn->query($sql)){ echo "<script>alert('บันทึกพัสดุเรียบร้อย'); window.location='manage_parcels.php';</script>"; }
}

// 2. ลูกบ้านมารับของแล้ว (Update)
if (isset($_GET['action']) && $_GET['action'] == 'pickup' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("UPDATE parcels SET status = 'picked_up', pickup_date = NOW() WHERE id = '$id'");
    echo "<script>alert('ยืนยันการรับของเรียบร้อย'); window.location='manage_parcels.php';</script>";
}

// 3. ดึงรายชื่อผู้เช่าปัจจุบัน (เอามาใส่ Dropdown)
$sql_users = "SELECT u.id as user_id, u.firstname, r.id as room_id, r.room_number 
              FROM bookings b JOIN users u ON b.user_id = u.id JOIN rooms r ON b.room_id = r.id 
              WHERE b.status = 'confirmed' ORDER BY r.room_number ASC";
$res_users = $conn->query($sql_users);

// 4. ดึงรายการพัสดุที่ "ยังไม่ได้รับ" (Waiting)
$sql_waiting = "SELECT p.*, u.firstname, r.room_number 
                FROM parcels p JOIN users u ON p.user_id = u.id JOIN rooms r ON p.room_id = r.id 
                WHERE p.status = 'waiting' ORDER BY p.arrived_at DESC";
$res_waiting = $conn->query($sql_waiting);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการพัสดุ - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid px-4"><span class="navbar-brand mb-0 h1 text-primary fw-bold">จัดการพัสดุไปรษณีย์</span></div>
    </nav>
    <div class="container-fluid px-4">
        <div class="row">
            <div class="col-md-3 col-lg-2 mb-4"><?php include 'sidebar.php'; ?></div>
            
            <div class="col-md-9 col-lg-10">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-primary text-white py-3 rounded-top-4"><h5 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>รับพัสดุใหม่</h5></div>
                            <div class="card-body p-4">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">เลือกห้อง / ผู้รับ</label>
                                        <select name="tenant_ref" class="form-select" required>
                                            <option value="" disabled selected>-- เลือกผู้เช่า --</option>
                                            <?php while($u = $res_users->fetch_assoc()): ?>
                                                <option value="<?php echo $u['user_id'].','.$u['room_id']; ?>">ห้อง <?php echo $u['room_number']; ?> - <?php echo $u['firstname']; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">รายละเอียดของ</label>
                                        <input type="text" name="description" class="form-control" placeholder="เช่น กล่อง Shopee, จดหมาย" required>
                                    </div>
                                    <button type="submit" name="add_parcel" class="btn btn-primary w-100 rounded-pill">บันทึก</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3 text-warning"><i class="bi bi-clock-history me-2"></i>พัสดุรอจ่าย (<?php echo $res_waiting->num_rows; ?> ชิ้น)</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light"><tr><th>ห้อง</th><th>ของ</th><th>มาถึงเมื่อ</th><th>จัดการ</th></tr></thead>
                                        <tbody>
                                            <?php while($row = $res_waiting->fetch_assoc()): ?>
                                            <tr>
                                                <td><span class="badge bg-primary"><?php echo $row['room_number']; ?></span> <small><?php echo $row['firstname']; ?></small></td>
                                                <td><?php echo $row['description']; ?></td>
                                                <td><small class="text-muted"><?php echo date('d/m H:i', strtotime($row['arrived_at'])); ?></small></td>
                                                <td>
                                                    <a href="?action=pickup&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success rounded-pill" onclick="return confirm('ลูกบ้านมารับของแล้วใช่ไหม?');">
                                                        <i class="bi bi-check-lg"></i> รับแล้ว
                                                    </a>
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
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>