<?php
session_start();
require_once '../connect.php';

// 1. เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. จัดการลบห้องพัก (Delete)
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    // (Optional) ถ้าอยากลบไฟล์รูปด้วย ให้ดึง path รูปมาเช็คก่อน แล้ว unlink() แต่ถ้าเอาแค่ลบข้อมูลก็ query delete ได้เลย
    $sql = "DELETE FROM rooms WHERE id = '$id'";
    if ($conn->query($sql)) {
        echo "<script>alert('ลบห้องพักเรียบร้อย'); window.location='manage_rooms.php';</script>";
    }
}

// 3. ดึงข้อมูลห้องทั้งหมด
$sql = "SELECT * FROM rooms ORDER BY room_number ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการห้องพัก - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>

    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-primary fw-bold">จัดการข้อมูลห้องพัก</span>
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
                            <h4 class="fw-bold mb-0">รายการห้องพักทั้งหมด</h4>
                            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                                <i class="bi bi-plus-lg me-2"></i>เพิ่มห้องพักใหม่
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>รูปภาพ</th>
                                        <th>เลขห้อง</th>
                                        <th>ชื่อห้อง/ประเภท</th>
                                        <th>ราคา/เดือน</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                        $img_path = $row['image'];
                                                        $display_img = "";

                                                        // เช็คว่าเป็นรูปที่อัปโหลดใหม่ (มีคำว่า uploads) หรือไม่
                                                        if (strpos($img_path, 'uploads/') !== false) {
                                                            // กรณีรูปใหม่: ต้องถอยหลัง 1 ขั้น (../) เพื่อออกจาก folder admin ไปหา uploads
                                                            $display_img = "../" . $img_path;
                                                        } else {
                                                            // กรณีรูปเก่า:
                                                            // ถ้าใน DB เก็บว่า "images/xxx.jpg" ให้เปลี่ยนเป็น "../images/xxx.jpg"
                                                            // ถ้าเก็บแค่ "xxx.jpg" ให้เติม "../images/" เข้าไปเลย
                                                            if (strpos($img_path, 'images/') !== false) {
                                                                $display_img = str_replace('images/', '../images/', $img_path);
                                                            } else {
                                                                $display_img = "../images/" . $img_path;
                                                            }
                                                        }
                                                    ?>
                                                    <img src="<?php echo $display_img; ?>" 
                                                         class="rounded border" 
                                                         style="width: 80px; height: 50px; object-fit: cover;"
                                                         onerror="this.src='https://via.placeholder.com/80x50?text=No+Image'"> 
                                                </td>
                                                <td class="fw-bold text-primary"><?php echo $row['room_number']; ?></td>
                                                <td>
                                                    <div><?php echo $row['room_name']; ?></div>
                                                    <small class="text-muted"><?php echo $row['room_type']; ?></small>
                                                </td>
                                                <td class="fw-bold">฿<?php echo number_format($row['price']); ?></td>
                                                <td>
                                                    <?php 
                                                        if($row['status'] == 'available') 
                                                            echo '<span class="badge bg-success">ว่าง</span>';
                                                        else 
                                                            echo '<span class="badge bg-secondary">ไม่ว่าง</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="room_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-warning rounded-pill me-1">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <a href="manage_rooms.php?delete_id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger rounded-pill"
                                                       onclick="return confirm('ยืนยันที่จะลบห้องพัก <?php echo $row['room_number']; ?> ?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-5">ยังไม่มีข้อมูลห้องพัก</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">เพิ่มห้องพักใหม่</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="room_save.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">เลขห้อง <span class="text-danger">*</span></label>
                                <input type="text" name="room_number" class="form-control" placeholder="เช่น A101" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">ชื่อห้องพัก</label>
                                <input type="text" name="room_name" class="form-control" placeholder="เช่น Standard Room" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">ราคาต่อเดือน (บาท)</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ขนาดห้อง (ตร.ม.)</label>
                                <input type="text" name="room_size" class="form-control" placeholder="เช่น 24 ตร.ม.">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ประเภท</label>
                                <select name="room_type" class="form-select">
                                    <option value="แอร์">แอร์</option>
                                    <option value="พัดลม">พัดลม</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">รูปภาพห้องพัก</label>
                                <input type="file" name="room_image" class="form-control" accept="image/*" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">รายละเอียดเพิ่มเติม</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">บันทึกข้อมูล</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>