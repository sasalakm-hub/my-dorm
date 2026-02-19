<?php
session_start();
require_once '../connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit(); }

// 1. รับค่าตัวกรองเดือนและปี (ถ้าไม่มีให้ใช้เดือนและปีปัจจุบัน)
$selected_month = isset($_GET['filter_month']) ? $_GET['filter_month'] : date('m');
$selected_year = isset($_GET['filter_year']) ? $_GET['filter_year'] : date('Y');

// 🔥 2. นำปีและเดือนมาต่อกันให้เป็นรูปแบบ YYYY-MM (เพื่อให้ตรงกับใน Database เป๊ะๆ)
$target_month = $selected_year . "-" . $selected_month; 

// 3. ดึงบิลที่จ่ายแล้วทั้งหมด พร้อมฟิลเตอร์ตามเดือนที่ต่อข้อความไว้
$sql = "SELECT b.*, u.firstname, u.lastname, r.room_number 
        FROM bills b 
        JOIN users u ON b.user_id = u.id 
        JOIN rooms r ON b.room_id = r.id 
        WHERE b.status = 'paid' 
        AND b.month = '$target_month' 
        ORDER BY b.pay_date DESC";
$result = $conn->query($sql);

// 4. เตรียมตัวแปรสำหรับเก็บยอดรวม... (โค้ดส่วนนี้และด้านล่างใช้ของเดิมได้เลยครับ)
$bills_data = [];
$total_income = 0;
$total_room = 0;
$total_water = 0;
$total_elec = 0;

if ($result) {
    while($row = $result->fetch_assoc()) {
        $bills_data[] = $row;
        $total_income += $row['total_price'];
        $total_room += $row['price_room'];
        $total_water += $row['water_price'];
        $total_elec += $row['elec_price'];
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานรายรับ - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; } 
        .summary-card { border: none; border-radius: 12px; transition: transform 0.2s; }
        .summary-card:hover { transform: translateY(-3px); }
        .icon-bg { opacity: 0.15; font-size: 4rem; position: absolute; right: 10px; bottom: -10px; }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-primary fw-bold"><i class="bi bi-graph-up-arrow me-2"></i>รายงานรายรับ / ประวัติการชำระเงิน</span>
        </div>
    </nav>

    <div class="container-fluid px-4 mb-5">
        <div class="row">
            
            <div class="col-md-3 col-lg-2 mb-4">
                <?php include 'sidebar.php'; ?>
            </div>

            <div class="col-md-9 col-lg-10">
                
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3">
                        <form action="" method="GET" class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="col-form-label fw-bold">เลือกเดือนที่ต้องการดู:</label>
                            </div>
                            <div class="col-md-3">
                                <select name="filter_month" class="form-select">
                                    <?php 
                                    $months = ["01"=>"มกราคม", "02"=>"กุมภาพันธ์", "03"=>"มีนาคม", "04"=>"เมษายน", "05"=>"พฤษภาคม", "06"=>"มิถุนายน", "07"=>"กรกฎาคม", "08"=>"สิงหาคม", "09"=>"กันยายน", "10"=>"ตุลาคม", "11"=>"พฤศจิกายน", "12"=>"ธันวาคม"];
                                    foreach($months as $key => $val) {
                                        $selected = ($selected_month == $key) ? "selected" : "";
                                        echo "<option value='$key' $selected>$val</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="filter_year" class="form-select">
                                    <?php 
                                    $current_year = date('Y');
                                    for($i = $current_year; $i >= $current_year - 2; $i--) { // โชว์ย้อนหลัง 2 ปี
                                        $selected = ($selected_year == $i) ? "selected" : "";
                                        echo "<option value='$i' $selected>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-2"></i>ค้นหา</button>
                                <a href="report_payments.php" class="btn btn-light border">เดือนปัจจุบัน</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card summary-card bg-primary text-white shadow-sm h-100 position-relative overflow-hidden">
                            <div class="card-body">
                                <h6 class="text-white-50 mb-1">รายรับรวมสุทธิ (บาท)</h6>
                                <h3 class="fw-bold mb-0">฿<?php echo number_format($total_income); ?></h3>
                                <i class="bi bi-wallet2 icon-bg text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card bg-success text-white shadow-sm h-100 position-relative overflow-hidden">
                            <div class="card-body">
                                <h6 class="text-white-50 mb-1">ยอดค่าห้อง (บาท)</h6>
                                <h3 class="fw-bold mb-0">฿<?php echo number_format($total_room); ?></h3>
                                <i class="bi bi-door-open icon-bg text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card bg-info text-white shadow-sm h-100 position-relative overflow-hidden">
                            <div class="card-body">
                                <h6 class="text-white-50 mb-1">ยอดค่าน้ำ (บาท)</h6>
                                <h3 class="fw-bold mb-0">฿<?php echo number_format($total_water); ?></h3>
                                <i class="bi bi-droplet icon-bg text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card bg-warning text-dark shadow-sm h-100 position-relative overflow-hidden">
                            <div class="card-body">
                                <h6 class="text-dark-50 mb-1" style="opacity:0.7">ยอดค่าไฟ (บาท)</h6>
                                <h3 class="fw-bold mb-0">฿<?php echo number_format($total_elec); ?></h3>
                                <i class="bi bi-lightning-charge icon-bg text-dark"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold">สัดส่วนรายได้เดือนนี้</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center pb-4">
                                <?php if($total_income > 0): ?>
                                    <canvas id="incomePieChart" width="100%"></canvas>
                                <?php else: ?>
                                    <div class="text-muted text-center py-5">
                                        <i class="bi bi-inbox fs-1"></i><br>ไม่มีข้อมูลในเดือนนี้
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0">รายละเอียดบิลที่ชำระแล้ว</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>วันที่ชำระ</th>
                                                <th>ห้อง</th>
                                                <th>ผู้จ่าย</th>
                                                <th>ค่าห้อง</th>
                                                <th>น้ำ+ไฟ</th>
                                                <th>ยอดสุทธิ</th>
                                                <th>สลิป</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(count($bills_data) > 0): ?>
                                                <?php foreach($bills_data as $row): ?>
                                                <tr>
                                                    <td>
                                                        <span class="d-block"><?php echo date('d/m/Y', strtotime($row['pay_date'])); ?></span>
                                                        <small class="text-muted"><?php echo date('H:i', strtotime($row['pay_date'])); ?></small>
                                                    </td>
                                                    <td><span class="badge bg-primary fs-6"><?php echo $row['room_number']; ?></span></td>
                                                    <td><?php echo $row['firstname']; ?></td>
                                                    <td class="text-muted"><?php echo number_format($row['price_room']); ?></td>
                                                    <td class="text-muted"><?php echo number_format($row['elec_price'] + $row['water_price']); ?></td>
                                                    <td class="fw-bold text-success">฿<?php echo number_format($row['total_price']); ?></td>
                                                    <td>
                                                        <a href="../uploads/bill_slips/<?php echo $row['slip_image']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                            <i class="bi bi-image"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="7" class="text-center text-muted py-4">ยังไม่มีการชำระเงินในเดือนที่เลือก</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div> </div> </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // โค้ดสร้างกราฟ (ทำงานเมื่อมียอดเงินมากกว่า 0 เท่านั้น)
        <?php if($total_income > 0): ?>
        const ctx = document.getElementById('incomePieChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['ค่าห้อง', 'ค่าน้ำ', 'ค่าไฟ'],
                datasets: [{
                    data: [
                        <?php echo $total_room; ?>, 
                        <?php echo $total_water; ?>, 
                        <?php echo $total_elec; ?>
                    ],
                    backgroundColor: ['#0d6efd', '#0dcaf0', '#ffc107'],
                    hoverOffset: 4,
                    borderWidth: 0
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
        <?php endif; ?>
    </script>
</body>
</html>