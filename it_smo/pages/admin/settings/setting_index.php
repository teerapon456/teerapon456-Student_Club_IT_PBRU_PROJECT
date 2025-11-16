<?php
$pageTitle = "ตั้งค่าระบบ | IT SMO";
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

?>

<div class="admin-settings">
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-1">
      </div>

      <!-- Main Content -->
      <div class="col-md-9 col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>ตั้งค่าระบบ</h2>
        </div>

        <!-- Settings Cards -->
        <div class="row g-4">
          <!-- General Settings -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i class="fas fa-cogs fa-2x text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h5 class="card-title mb-0">ตั้งค่าทั่วไป</h5>
                  </div>
                </div>
                <p class="card-text text-muted">จัดการการตั้งค่าพื้นฐานของระบบ เช่น ชื่อระบบ, อีเมลติดต่อ, การตั้งค่าการใช้งาน</p>
                <a href="general.php" class="btn btn-primary">
                  <i class="fas fa-arrow-right"></i> จัดการ
                </a>
              </div>
            </div>
          </div>

          <!-- Role Settings -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i class="fas fa-user-shield fa-2x text-success"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h5 class="card-title mb-0">ตั้งค่าบทบาท</h5>
                  </div>
                </div>
                <p class="card-text text-muted">จัดการบทบาทและสิทธิ์การใช้งานของผู้ใช้ในระบบ</p>
                <a href="roles.php" class="btn btn-success">
                  <i class="fas fa-arrow-right"></i> จัดการ
                </a>
              </div>
            </div>
          </div>

          <!-- System Logs -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i class="fas fa-history fa-2x text-info"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h5 class="card-title mb-0">ประวัติการใช้งาน</h5>
                  </div>
                </div>
                <p class="card-text text-muted">ดูประวัติการใช้งานระบบของผู้ใช้ทั้งหมด</p>
                <a href="logs.php" class="btn btn-info">
                  <i class="fas fa-arrow-right"></i> จัดการ
                </a>
              </div>
            </div>
          </div>

          <!-- Backup Settings -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i class="fas fa-database fa-2x text-warning"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h5 class="card-title mb-0">สำรองข้อมูล</h5>
                  </div>
                </div>
                <p class="card-text text-muted">จัดการการสำรองข้อมูลและกู้คืนข้อมูลของระบบ</p>
                <a href="backup.php" class="btn btn-warning">
                  <i class="fas fa-arrow-right"></i> จัดการ
                </a>
              </div>
            </div>
          </div>

          <!-- Email Settings -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i class="fas fa-envelope fa-2x text-danger"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h5 class="card-title mb-0">ตั้งค่าอีเมล</h5>
                  </div>
                </div>
                <p class="card-text text-muted">จัดการการตั้งค่าการส่งอีเมลและการแจ้งเตือน</p>
                <a href="email.php" class="btn btn-danger">
                  <i class="fas fa-arrow-right"></i> จัดการ
                </a>
              </div>
            </div>
          </div>

          <!-- Security Settings -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i class="fas fa-shield-alt fa-2x text-secondary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h5 class="card-title mb-0">ความปลอดภัย</h5>
                  </div>
                </div>
                <p class="card-text text-muted">จัดการการตั้งค่าความปลอดภัยของระบบ</p>
                <a href="security.php" class="btn btn-secondary">
                  <i class="fas fa-arrow-right"></i> จัดการ
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../../../includes/admin_footer.php'; ?> 