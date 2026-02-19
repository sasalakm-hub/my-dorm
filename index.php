<?php 
session_start(); 
require_once 'connect.php'; // 1. เรียกใช้ไฟล์เชื่อมต่อ Database

// 2. ฟังก์ชันช่วยนับห้องว่าง (เขียนไว้ตรงนี้จะได้เรียกใช้ง่ายๆ)
function countAvailableRooms($conn, $building_prefix) {
    $sql = "SELECT COUNT(*) as count 
            FROM rooms 
            WHERE room_number LIKE :prefix 
            AND status = 'available'";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':prefix' => $building_prefix . '%'
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? $row['count'] : 0;
}


// 3. ดึงจำนวนห้องว่างมาเก็บใส่ตัวแปร
$count_A = countAvailableRooms($conn, 'A');
$count_B = countAvailableRooms($conn, 'B');
$count_C = countAvailableRooms($conn, 'C');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หอพักแสนสุข - หน้าแรก</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="alert alert-success border-0 rounded-0 d-flex align-items-center justify-content-center py-2" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> 
        ยินดีต้อนรับกลับ, คุณ <?php echo $_SESSION['firstname'] . " " . $_SESSION['lastname']; ?> เข้าสู่ระบบหอพักแสนสุข
    </div>
    <?php endif; ?>

    <?php include 'navbar.php'; ?>

    <header class="hero-banner mb-5">
        <div class="container">
            <h1 class="display-4">ยกระดับชีวิตนักศึกษา ที่ "หอพักแสนสุข"</h1>
            <p class="lead mb-4">ความสะดวกสบายที่ลงตัว ปลอดภัย อบอุ่นเหมือนอยู่บ้าน</p>
        </div>
    </header>

    <section class="container py-5 bg-white rounded-4 shadow-sm mb-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="images/room.jpg" alt="บรรยากาศหอพัก" class="img-fluid rounded-4 shadow">
            </div>  
            <div class="col-lg-6 ps-lg-5">
                <h6 class="text-primary text-uppercase fw-bold ls-md">เกี่ยวกับเรา</h6>
                <h2 class="mb-4 fw-bold">ทำไมต้องเลือก หอพักแสนสุข?</h2>
                <p class="text-muted fs-5 mb-4">
                    เราเข้าใจความต้องการของคนรุ่นใหม่ หอพักของเราจึงออกแบบมาเพื่อตอบโจทย์ทั้งการพักผ่อนและการเรียนรู้ ด้วยทำเลที่ตั้งที่เดินทางสะดวก ใกล้มหาวิทยาลัย และรายล้อมด้วยสิ่งอำนวยความสะดวกครบครัน
                </p>
                
                <div class="row g-4 mt-3">
                    <div class="col-6 col-md-6 feature-box bg-light rounded-3">
                        <i class="bi bi-wifi feature-icon"></i>
                        <h5 class="fw-bold">ฟรี High-Speed WiFi</h5>
                    </div>
                    <div class="col-6 col-md-6 feature-box bg-light rounded-3">
                        <i class="bi bi-shield-check feature-icon"></i>
                        <h5 class="fw-bold">ปลอดภัย 24 ชม.</h5>
                    </div>
                    <div class="col-6 col-md-6 feature-box bg-light rounded-3">
                        <i class="bi bi-shop feature-icon"></i>
                        <h5 class="fw-bold">ใกล้ร้านสะดวกซื้อ</h5>
                    </div>
                     <div class="col-6 col-md-6 feature-box bg-light rounded-3">
                        <i class="bi bi-p-circle feature-icon"></i>
                        <h5 class="fw-bold">ที่จอดรถกว้างขวาง</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-5">
        <div class="text-center mb-5">
            <h6 class="text-primary text-uppercase fw-bold ls-md">ห้องพักของเรา</h6>
            <h2 class="fw-bold">เลือกห้องที่เหมาะกับไลฟ์สไตล์ของคุณ</h2>
        </div>

        <div class="row g-4">
            
            <div class="col-md-4">
                <div class="card room-card h-100 shadow-sm">
                    <div class="position-relative">
                        <?php if($count_A > 0): ?>
                            <span class="position-absolute top-0 start-0 bg-success text-white px-3 py-2 m-3 rounded-pill fw-bold fs-6">
                                <i class="bi bi-check-circle me-1"></i>ว่าง <?php echo $count_A; ?> ห้อง
                            </span>
                        <?php else: ?>
                            <span class="position-absolute top-0 start-0 bg-secondary text-white px-3 py-2 m-3 rounded-pill fw-bold fs-6">
                                <i class="bi bi-x-circle me-1"></i>เต็มแล้ว
                            </span>
                        <?php endif; ?>
                        
                        <img src="images/room2.jpg" class="card-img-top" alt="ห้อง Standard">
                    </div>

                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title fw-bold mb-0">Type A: ห้องมาตรฐาน</h5>
                            <span class="badge bg-info text-dark"><i class="bi bi-snow"></i> แอร์</span>
                        </div>
                        <p class="card-text text-muted mb-4">ขนาด 24 ตร.ม. พร้อมเฟอร์นิเจอร์ครบชุด เตียง ตู้ โต๊ะทำงาน เหมาะสำหรับอยู่ 1-2 คน</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="price-tag">฿4,500 <small class="text-muted fs-6">/ เดือน</small></div>
                            <a href="rooms.php?page=1" class="btn btn-outline-primary rounded-pill px-4 stretched-link">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card room-card h-100 shadow-sm">
                     <div class="position-relative">
                        <?php if($count_B > 0): ?>
                            <span class="position-absolute top-0 start-0 bg-success text-white px-3 py-2 m-3 rounded-pill fw-bold fs-6">
                                <i class="bi bi-check-circle me-1"></i>ว่าง <?php echo $count_B; ?> ห้อง
                            </span>
                        <?php else: ?>
                            <span class="position-absolute top-0 start-0 bg-secondary text-white px-3 py-2 m-3 rounded-pill fw-bold fs-6">
                                <i class="bi bi-x-circle me-1"></i>เต็มแล้ว
                            </span>
                        <?php endif; ?>

                        <img src="images/room3.jpg" class="card-img-top" alt="ห้อง Suite">
                    </div>

                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title fw-bold mb-0">Type B: ห้องสวีท (มุม)</h5>
                             <span class="badge bg-info text-dark"><i class="bi bi-snow"></i> แอร์</span>
                        </div>
                        <p class="card-text text-muted mb-4">ขนาด 32 ตร.ม. ห้องมุม วิวสวย มีระเบียงกว้างพิเศษ และโซนครัวเล็กๆ</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="price-tag">฿5,800 <small class="text-muted fs-6">/ เดือน</small></div>
                            <a href="rooms.php?page=2" class="btn btn-outline-primary rounded-pill px-4 stretched-link">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card room-card h-100 shadow-sm">
                    <div class="position-relative">
                        <?php if($count_C > 0): ?>
                            <span class="position-absolute top-0 start-0 bg-success text-white px-3 py-2 m-3 rounded-pill fw-bold fs-6">
                                <i class="bi bi-check-circle me-1"></i>ว่าง <?php echo $count_C; ?> ห้อง
                            </span>
                        <?php else: ?>
                            <span class="position-absolute top-0 start-0 bg-secondary text-white px-3 py-2 m-3 rounded-pill fw-bold fs-6">
                                <i class="bi bi-x-circle me-1"></i>เต็มแล้ว
                            </span>
                        <?php endif; ?>

                        <img src="images/room4.jpg" class="card-img-top" alt="ห้อง Economy">
                    </div>

                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title fw-bold mb-0">Type C: ห้องประหยัด</h5>
                             <span class="badge bg-warning text-dark"><i class="bi bi-fan"></i> พัดลม</span>
                        </div>
                        <p class="card-text text-muted mb-4">ขนาด 20 ตร.ม. ห้องพัดลม ราคาประหยัด แต่ยังคงความสะดวกสบายและสะอาด</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="price-tag">฿3,200 <small class="text-muted fs-6">/ เดือน</small></div>
                            <a href="rooms.php?page=3" class="btn btn-outline-primary rounded-pill px-4 stretched-link">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            </div>

        </div> 
        
        <div class="text-center mt-5">
            <a href="rooms.php" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">ดูห้องพักทั้งหมด <i class="bi bi-arrow-right"></i></a>
        </div>

    </section>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">© 2025 หอพักแสนสุข. ระบบจัดการหอพักโดยนักศึกษาวิศวะคอมฯ</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>