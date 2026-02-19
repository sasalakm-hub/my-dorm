<?php
session_start();
require_once '../connect.php'; 

// 1. เช็คความปลอดภัย: ถ้าไม่ใช่ admin ห้ามเข้า!
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. ดึงข้อมูลสรุป (Stats) สำหรับ Card ด้านบน
$sql_booking = "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'";
$res_booking = $conn->query($sql_booking);
$count_booking = $res_booking->fetch_assoc()['count'];

 // อิงตามชื่อสถานะใน DB ของคุณ ('ว่าง', 'ไม่ว่าง', 'จองแล้ว')
$sql_room = "SELECT COUNT(*) as count FROM rooms WHERE status = 'available'";
$res_room = $conn->query($sql_room);
$count_room = $res_room->fetch_assoc()['count'];

$sql_user = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
$res_user = $conn->query($sql_user);
$count_user = $res_user->fetch_assoc()['count'];

// ---------------------------------------------------------
// 3. ดึงข้อมูลสำหรับทำกราฟ (New!)
// ---------------------------------------------------------

// กราฟที่ 1: สถานะห้องพัก (นับแยกตามสถานะ)
$sql_room_status = "SELECT status, COUNT(*) as count FROM rooms GROUP BY status";
$res_room_status = $conn->query($sql_room_status);

// เตรียมตัวแปรเก็บค่าเริ่มต้น (เผื่อบางสถานะยังไม่มีข้อมูล)
$room_stats = ['available' => 0, 'busy' => 0, 'booked' => 0];
if($res_room_status) {
    while($row = $res_room_status->fetch_assoc()){
        // เอาค่าจาก DB มาใส่ใน Array ตามชื่อสถานะ
        $room_stats[$row['status']] = $row['count']; 
    }
}

// กราฟที่ 2: ข้อมูลรายได้ (ตอนนี้ใช้ข้อมูลจำลองไปก่อน ถ้าระบบบิลเสร็จค่อยมาแก้ Query ตรงนี้)
$revenue_labels = "['ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.']";
$revenue_data = "[32000, 35000, 31500, 42000, 38000, 45000]"; 
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - หอพักแสนสุข</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; }
        .stat-card { transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-primary fw-bold"><i class="bi bi-building-fill me-2"></i>ระบบจัดการหอพัก</span>
            <span class="text-muted small">ยินดีต้อนรับ, Admin</span>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            
            <div class="col-md-3 col-lg-2 mb-4">
                <?php include 'sidebar.php'; ?>
            </div>

            <div class="col-md-9 col-lg-10">
                <h3 class="fw-bold mb-4">ภาพรวมระบบ</h3>

                <div class="row g-4">
                    
                    <div class="col-md-4">
                        <div class="card stat-card border-0 shadow-sm h-100 bg-warning bg-opacity-10">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted text-uppercase mb-2">รอตรวจสอบสลิป</h6>
                                        <h2 class="fw-bold text-warning mb-0"><?php echo $count_booking; ?> รายการ</h2>
                                    </div>
                                    <div class="bg-warning text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-hourglass-split fs-3"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="manage_bookings.php" class="btn btn-sm btn-outline-warning rounded-pill px-3">จัดการทันที <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card stat-card border-0 shadow-sm h-100 bg-success bg-opacity-10">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted text-uppercase mb-2">ห้องว่างพร้อมอยู่</h6>
                                        <h2 class="fw-bold text-success mb-0"><?php echo $count_room; ?> ห้อง</h2>
                                    </div>
                                    <div class="bg-success text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-house-check fs-3"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="manage_rooms.php" class="btn btn-sm btn-outline-success rounded-pill px-3">ดูข้อมูลห้อง <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card stat-card border-0 shadow-sm h-100 bg-primary bg-opacity-10">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted text-uppercase mb-2">ผู้เช่าในระบบ</h6>
                                        <h2 class="fw-bold text-primary mb-0"><?php echo $count_user; ?> คน</h2>
                                    </div>
                                    <div class="bg-primary text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-people-fill fs-3"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="manage_users.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">ดูรายชื่อ <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <div class="row g-4 mt-2 mb-5">
                    
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold"><i class="bi bi-bar-chart-line text-primary me-2"></i> สถิติรายรับย้อนหลัง 6 เดือน</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold"><i class="bi bi-pie-chart text-success me-2"></i> สถานะห้องพักทั้งหมด</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <canvas id="roomStatusChart"></canvas>
                            </div>
                        </div>
                    </div>

                </div> </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // --- 1. กราฟแท่ง (รายได้) ---
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: <?php echo $revenue_labels; ?>, // ดึงจาก PHP
                datasets: [{
                    label: 'รายรับ (บาท)',
                    data: <?php echo $revenue_data; ?>, // ดึงจาก PHP
                    backgroundColor: '#0d6efd',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });

        // --- 2. กราฟวงกลม (สถานะห้องพัก) ---
        const ctxRoom = document.getElementById('roomStatusChart').getContext('2d');
        new Chart(ctxRoom, {
            type: 'doughnut',
            data: {
                labels: ['ว่าง (Available)', 'ไม่ว่าง (Busy)', 'จองแล้ว (Booked)'],
                datasets: [{
                    // 🔥 แก้ชื่อตัวแปรในวงเล็บ [] ให้ตรงกับที่ตั้งไว้ข้างบน
                    data: [
                        <?php echo $room_stats['available']; ?>, 
                        <?php echo $room_stats['busy']; ?>, 
                        <?php echo $room_stats['booked']; ?>
                    ],
                    backgroundColor: ['#198754', '#6c757d', '#ffc107'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>