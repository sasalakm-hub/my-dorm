<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-success fs-3" href="index.php">
            <i class="bi bi-building-fill me-2"></i>หอพักแสนสุข
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rooms.php">ห้องพักทั้งหมด</a>
                </li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle btn btn-light rounded-pill px-3 text-dark border" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle text-primary me-2"></i> 
                            สวัสดี, <strong><?php echo $_SESSION['firstname']; ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                            <li>
                                <span class="dropdown-item-text text-muted small">
                                    <i class="bi bi-person-badge me-2"></i>สถานะ: <?php echo ucfirst($_SESSION['role']); ?>
                                </span>
                            </li>
                            <li>
                                <hr class="dropdown-divider"> 
                            </li>

                            <li>
                                <a class="dropdown-item" href="booking_history.php">
                                    <i class="bi bi-clock-history me-2"></i>ประวัติการจอง
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php" onclick="return confirm('ยืนยันการออกจากระบบ?');">
                                    <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="user_dashboard.php">
                                     <i class="bi bi-speedometer2 me-2"></i>Dashboard ของฉัน
                                 </a>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>

                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-primary rounded-pill px-4 me-2" href="login.php">เข้าสู่ระบบ</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary rounded-pill px-4" href="register.php">สมัครสมาชิก</a>
                    </li>

                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>