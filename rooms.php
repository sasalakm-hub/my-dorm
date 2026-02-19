<?php
session_start();
require_once 'connect.php';

// --- กำหนดตัวแปรสำหรับตรวจสอบโหมด ---
$is_search = false; // ค่าเริ่มต้นคือ ไม่ได้ค้นหา (โหมดดูตามตึก)
$search_sql = "";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$building_name = "";

// --- เช็คว่ามีการกดปุ่ม "ค้นหา" มาหรือไม่ ---
if (isset($_GET['action']) && $_GET['action'] == 'search') {
    $is_search = true; // เปลี่ยนเข้าสู่โหมดค้นหา
    
    // สร้าง SQL สำหรับการค้นหา
    $conditions = [];
    
    // 1. คำค้นหา (keyword)
    if (!empty($_GET['keyword'])) {
        $kw = $conn->real_escape_string($_GET['keyword']);
        $conditions[] = "(room_number LIKE '%$kw%' OR room_name LIKE '%$kw%' OR room_type LIKE '%$kw%')";
    }
    
    // 2. ราคา (price_range)
    if (!empty($_GET['price_range'])) {
        $range = $_GET['price_range'];
        if ($range == '1') $conditions[] = "price < 3500";
        elseif ($range == '2') $conditions[] = "price BETWEEN 3500 AND 5000";
        elseif ($range == '3') $conditions[] = "price > 5000";
    }
    
    // 3. สถานะ (status)
    if (!empty($_GET['status'])) {
        $st = $conn->real_escape_string($_GET['status']);
        $conditions[] = "status = '$st'";
    }
    
    $sql = "SELECT * FROM rooms";
    if (count($conditions) > 0) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " ORDER BY status ASC, room_number ASC";
    
} else {
    // --- โหมดปกติ (ไม่ได้ค้นหา): แสดงตามตึก A, B, C ---
    
    $building = 'A';      // หน้า 1 หา A
    $building_name = "โซน A";

    if ($page == 2) {
        $building = 'B';  // หน้า 2 หา B
        $building_name = "โซน B";
    } elseif ($page == 3) {
        $building = 'C';  // หน้า 3 หา C
        $building_name = "โซน C";
    }

    $sql = "SELECT * FROM rooms WHERE room_number LIKE '$building%' ORDER BY room_number ASC";
}

// รันคำสั่ง SQL (ไม่ว่าจะโหมดไหน ก็ใช้ตัวแปร $result ตัวเดิม)
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ห้องพักทั้งหมด - หอพักแสนสุข</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <header class="page-header d-flex align-items-center" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/room.jpg') no-repeat center center; background-size: cover; min-height: 300px;">
        <div class="container text-center">
            <h1 class="fw-bold display-4 text-white mb-2" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.6);">ห้องพักของเรา</h1>
            <p class="lead text-white-50 mb-0">เลือกห้องพักที่ตรงใจ ในบรรยากาศที่คุณชอบ</p>
        </div>
    </header>

    <div class="container pb-5">
        
        <div class="filter-bar mb-5 card border-0 shadow-sm p-4 rounded-4">
            <form action="rooms.php" method="GET">
                <input type="hidden" name="action" value="search"> <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-bold">ค้นหา</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="keyword" class="form-control border-start-0 ps-0" 
                                   placeholder="ชื่อห้อง / ประเภท..." 
                                   value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-bold">ช่วงราคา</label>
                        <select name="price_range" class="form-select">
                            <option value="">ทุกราคา</option>
                            <option value="1" <?php if(isset($_GET['price_range']) && $_GET['price_range']=='1') echo 'selected'; ?>>น้อยกว่า 3,500 บาท</option>
                            <option value="2" <?php if(isset($_GET['price_range']) && $_GET['price_range']=='2') echo 'selected'; ?>>3,500 - 5,000 บาท</option>
                            <option value="3" <?php if(isset($_GET['price_range']) && $_GET['price_range']=='3') echo 'selected'; ?>>มากกว่า 5,000 บาท</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-bold">สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="available" <?php if(isset($_GET['status']) && $_GET['status']=='available') echo 'selected'; ?>>ว่าง</option>
                            <option value="busy" <?php if(isset($_GET['status']) && $_GET['status']=='busy') echo 'selected'; ?>>ไม่ว่าง</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-none d-md-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">ค้นหา</button>
                    </div>
                </div>
                
                <?php if($is_search): ?>
                    <div class="row mt-2">
                        <div class="col-12 text-end">
                            <a href="rooms.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-counterclockwise"></i> ล้างค่า / ดูห้องตามตึก</a>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="row g-4">
            
            <div class="col-12">
                <?php if($is_search): ?>
                    <h3 class='text-primary border-start border-4 border-primary ps-3'>
                        ผลการค้นหา (<?php echo $result->num_rows; ?> ห้อง)
                    </h3>
                <?php else: ?>
                    <h3 class='text-primary border-start border-4 border-primary ps-3'>
                        ห้องพัก <?php echo $building_name; ?>
                    </h3>
                <?php endif; ?>
            </div>

            <?php
            if ($result->num_rows > 0) {
                while($room = $result->fetch_assoc()) : 
                    
                    // Logic การแสดงผลเหมือนเดิม
                    $status_badge = ($room['status'] == 'available') 
                        ? '<span class="position-absolute top-0 start-0 bg-success text-white px-3 py-2 m-3 rounded-pill fw-bold small shadow-sm"><i class="bi bi-check-circle me-1"></i>ว่างพร้อมอยู่</span>' 
                        : '<span class="position-absolute top-0 start-0 bg-secondary text-white px-3 py-2 m-3 rounded-pill fw-bold small shadow-sm"><i class="bi bi-x-circle me-1"></i>ไม่ว่าง</span>';
                    
                    $btn_disabled = ($room['status'] == 'busy') ? 'disabled btn-secondary' : 'btn-outline-primary';
                    $btn_text = ($room['status'] == 'busy') ? 'เต็ม' : 'ดูรายละเอียด';
                    $modalID = "roomModal-" . $room['id'];

                    // จัดการ Path รูปภาพ (ใส่เพิ่มให้เพื่อความชัวร์)
                    $img_src = $room['image'];
                    if(strpos($img_src, '/') === false) { $img_src = "images/" . $img_src; }
            ?>
            
            <div class="col-md-6 col-lg-4">
                <div class="card room-card h-100 shadow-sm border-0 rounded-4">
                    <div class="position-relative overflow-hidden rounded-top-4">
                        <?php echo $status_badge; ?>
                        <img src="<?php echo $img_src; ?>" class="card-img-top" alt="<?php echo $room['room_name']; ?>" 
                             style="height: 240px; object-fit: cover;"
                             onerror="this.src='https://via.placeholder.com/400x240?text=No+Image'">
                    </div>

                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title fw-bold text-dark mb-0"><?php echo $room['room_number'] . " : " . $room['room_name']; ?></h5>
                        </div>
                        
                        <ul class="room-amenities list-unstyled d-flex gap-3 text-muted small mb-3">
                            <li><i class="bi bi-aspect-ratio"></i> <?php echo $room['room_size']; ?></li>
                            <li><i class="bi bi-snow"></i> <?php echo $room['room_type']; ?></li>
                        </ul>
                        
                        <hr class="text-muted opacity-25">

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span class="text-muted small">ราคาเริ่มต้น</span>
                                <div class="price-tag text-primary fw-bold fs-5">฿<?php echo number_format($room['price']); ?></div>
                            </div>
                            
                            <button type="button" 
                                    class="btn <?php echo $btn_disabled; ?> rounded-pill px-4" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#<?php echo $modalID; ?>">
                                <?php echo $btn_text; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="<?php echo $modalID; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg"> 
                    <div class="modal-content rounded-4 border-0">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold fs-4"><?php echo $room['room_number'] . " : " . $room['room_name']; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <img src="<?php echo $img_src; ?>" class="img-fluid rounded-3 shadow-sm w-100" style="height: 250px; object-fit: cover;" alt="">
                                </div>
                                <div class="col-md-6">
                                    <h4 class="text-primary fw-bold mb-3">฿<?php echo number_format($room['price']); ?> <span class="fs-6 text-muted fw-normal">/ เดือน</span></h4>
                                    <p class="text-muted"><?php echo isset($room['description']) ? $room['description'] : 'รายละเอียดห้องพักเพิ่มเติม...'; ?></p>
                                    
                                    <div class="bg-light p-3 rounded-3 mb-3">
                                        <div class="d-flex justify-content-between mb-2"><span>ขนาด</span><span class="fw-bold"><?php echo $room['room_size']; ?></span></div>
                                        <div class="d-flex justify-content-between mb-2"><span>ประเภท</span><span class="fw-bold"><?php echo $room['room_type']; ?></span></div>
                                        <div class="d-flex justify-content-between"><span>ค่าไฟ</span><span class="fw-bold">7 บาท/หน่วย</span></div>
                                    </div>
                                    
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <a href="booking.php?room_id=<?php echo $room['id']; ?>" class="btn btn-success w-100 rounded-pill py-2">
                                            <i class="bi bi-bookmark-check me-2"></i>จองห้องนี้ทันที
                                        </a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-secondary w-100 rounded-pill py-2" onclick="return confirm('กรุณาเข้าสู่ระบบก่อนทำการจองห้องพัก');">
                                            <i class="bi bi-lock-fill me-2"></i>เข้าสู่ระบบเพื่อจอง
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php 
                endwhile; 
            } else {
                echo "<div class='col-12 text-center text-muted py-5'>
                        <i class='bi bi-search display-1 text-light'></i><br>
                        ไม่พบข้อมูลห้องพัก " . ($is_search ? "ตามเงื่อนไขที่ค้นหา" : "ในโซน $building_name") . "
                      </div>";
            }
            ?>

        </div> 
        
        <?php if(!$is_search): ?>
        <nav class="mt-5" aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php 
                    $prev_link = ($page == 1) ? "index.php" : "?page=" . ($page - 1);
                ?>
                <li class="page-item"><a class="page-link rounded-pill mx-1" href="<?php echo $prev_link; ?>">ก่อนหน้า</a></li>
                <li class="page-item <?php if($page == 1) echo 'active'; ?>"><a class="page-link rounded-pill mx-1" href="?page=1">ห้อง A</a></li>
                <li class="page-item <?php if($page == 2) echo 'active'; ?>"><a class="page-link rounded-pill mx-1" href="?page=2">ห้อง B</a></li>
                <li class="page-item <?php if($page == 3) echo 'active'; ?>"><a class="page-link rounded-pill mx-1" href="?page=3">ห้อง C</a></li>
                <li class="page-item <?php if($page >= 3) echo 'disabled'; ?>"><a class="page-link rounded-pill mx-1" href="?page=<?php echo $page+1; ?>">ถัดไป</a></li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>

    <footer class="bg-white text-center py-4 border-top mt-auto">
        <div class="container">
            <p class="mb-0 text-muted">© 2025 หอพักแสนสุข</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>