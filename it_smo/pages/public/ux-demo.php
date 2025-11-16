<?php
$pageTitle = "UX Demo - การปรับปรุง User Experience | IT SMO";
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
  <div class="row">
    <div class="col-12">
      <h1 class="text-center mb-5">🎨 การปรับปรุง User Experience</h1>
      
      <!-- Enhanced Forms Demo -->
      <div class="form-container mb-5">
        <div class="form-header">
          <h2 class="form-title">📝 ฟอร์มที่ปรับปรุงแล้ว</h2>
          <p class="form-subtitle">ฟอร์มที่มีการตรวจสอบข้อมูลแบบ Real-time และ Loading States</p>
        </div>
        
        <form class="needs-validation" novalidate>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="firstName">ชื่อ <span class="required">*</span></label>
                <div class="input-group">
                  <span class="input-group-icon">
                    <i class="fas fa-user"></i>
                  </span>
                  <input type="text" class="form-control" id="firstName" data-validate="text" required>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="lastName">นามสกุล <span class="required">*</span></label>
                <div class="input-group">
                  <span class="input-group-icon">
                    <i class="fas fa-user"></i>
                  </span>
                  <input type="text" class="form-control" id="lastName" data-validate="text" required>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="email">อีเมล <span class="required">*</span></label>
                <div class="input-group">
                  <span class="input-group-icon">
                    <i class="fas fa-envelope"></i>
                  </span>
                  <input type="email" class="form-control" id="email" data-validate="email" required>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="phone">เบอร์โทรศัพท์</label>
                <div class="input-group">
                  <span class="input-group-icon">
                    <i class="fas fa-phone"></i>
                  </span>
                  <input type="tel" class="form-control" id="phone" data-validate="phone">
                </div>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label" for="studentId">รหัสนักศึกษา <span class="required">*</span></label>
            <div class="input-group">
              <span class="input-group-icon">
                <i class="fas fa-id-card"></i>
              </span>
              <input type="text" class="form-control" id="studentId" data-validate="student-id" required>
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label" for="password">รหัสผ่าน <span class="required">*</span></label>
            <div class="input-group">
              <span class="input-group-icon">
                <i class="fas fa-lock"></i>
              </span>
              <input type="password" class="form-control" id="password" data-validate="password" required>
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label" for="major">สาขาวิชา</label>
            <select class="form-select" id="major">
              <option value="">เลือกสาขาวิชา</option>
              <option value="it">เทคโนโลยีสารสนเทศ</option>
              <option value="cs">วิทยาการคอมพิวเตอร์</option>
              <option value="se">วิศวกรรมซอฟต์แวร์</option>
            </select>
          </div>
          
          <div class="form-group">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="agree" required>
              <label class="form-check-label" for="agree">
                ฉันยอมรับ <a href="#" class="text-primary">เงื่อนไขการใช้งาน</a>
              </label>
            </div>
          </div>
          
          <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg" data-loading data-loading-text="กำลังบันทึกข้อมูล...">
              <i class="fas fa-save me-2"></i>บันทึกข้อมูล
            </button>
            <button type="button" class="btn btn-secondary btn-lg ms-3" onclick="showNotification('ทดสอบการแจ้งเตือน', 'success')">
              <i class="fas fa-bell me-2"></i>ทดสอบการแจ้งเตือน
            </button>
          </div>
        </form>
      </div>
      
      <!-- Enhanced Cards Demo -->
      <div class="row mb-5">
        <div class="col-md-4">
          <div class="card dashboard-card h-100">
            <div class="card-body text-center">
              <div class="stat-icon bg1 mb-3">
                <i class="fas fa-users"></i>
              </div>
              <h3 class="fw-bold">1,234</h3>
              <p class="text-muted">สมาชิกทั้งหมด</p>
              <div class="progress mb-2">
                <div class="progress-bar" role="progressbar" style="width: 75%" data-width="75%"></div>
              </div>
              <small class="text-success">+12% จากเดือนที่แล้ว</small>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card dashboard-card h-100">
            <div class="card-body text-center">
              <div class="stat-icon bg2 mb-3">
                <i class="fas fa-file-alt"></i>
              </div>
              <h3 class="fw-bold">567</h3>
              <p class="text-muted">เอกสารทั้งหมด</p>
              <div class="progress mb-2">
                <div class="progress-bar" role="progressbar" style="width: 60%" data-width="60%"></div>
              </div>
              <small class="text-info">+8% จากเดือนที่แล้ว</small>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card dashboard-card h-100">
            <div class="card-body text-center">
              <div class="stat-icon bg3 mb-3">
                <i class="fas fa-calendar-check"></i>
              </div>
              <h3 class="fw-bold">89</h3>
              <p class="text-muted">กิจกรรมทั้งหมด</p>
              <div class="progress mb-2">
                <div class="progress-bar" role="progressbar" style="width: 45%" data-width="45%"></div>
              </div>
              <small class="text-warning">+5% จากเดือนที่แล้ว</small>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Loading States Demo -->
      <div class="row mb-5">
        <div class="col-12">
          <h3 class="mb-4">⏳ การแสดง Loading States</h3>
          <div class="d-flex gap-3 flex-wrap">
            <button class="btn btn-primary" data-loading data-loading-text="กำลังโหลด...">
              <i class="fas fa-download me-2"></i>ดาวน์โหลด
            </button>
            <button class="btn btn-success" data-loading data-loading-text="กำลังบันทึก...">
              <i class="fas fa-save me-2"></i>บันทึก
            </button>
            <button class="btn btn-info" data-loading data-loading-text="กำลังส่ง...">
              <i class="fas fa-paper-plane me-2"></i>ส่งข้อมูล
            </button>
            <button class="btn btn-warning" onclick="showGlobalLoading()">
              <i class="fas fa-spinner me-2"></i>แสดง Global Loading
            </button>
          </div>
        </div>
      </div>
      
      <!-- Responsive Demo -->
      <div class="row mb-5">
        <div class="col-12">
          <h3 class="mb-4">📱 การตอบสนองบนอุปกรณ์ต่างๆ</h3>
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            ลองปรับขนาดหน้าต่างเบราว์เซอร์เพื่อดูการปรับตัวของระบบ
          </div>
          
          <div class="grid-responsive">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">การปรับตัวของ Grid</h5>
                <p class="card-text">ระบบจะปรับจำนวนคอลัมน์ตามขนาดหน้าจอ</p>
              </div>
            </div>
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">การปรับตัวของ Typography</h5>
                <p class="card-text">ขนาดตัวอักษรจะปรับตามขนาดหน้าจอ</p>
              </div>
            </div>
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">การปรับตัวของ Spacing</h5>
                <p class="card-text">ระยะห่างจะปรับตามขนาดหน้าจอ</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Animation Demo -->
      <div class="row mb-5">
        <div class="col-12">
          <h3 class="mb-4">🎬 เอฟเฟกต์และแอนิเมชั่น</h3>
          <div class="row">
            <div class="col-md-6">
              <div class="card animate-on-scroll">
                <div class="card-body">
                  <h5 class="card-title">Card ที่มี Animation</h5>
                  <p class="card-text">การ์ดนี้จะมีเอฟเฟกต์เมื่อเลื่อนมาถึง</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card animate-on-scroll">
                <div class="card-body">
                  <h5 class="card-title">Hover Effects</h5>
                  <p class="card-text">ลอง hover ที่การ์ดเพื่อดูเอฟเฟกต์</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Demo functions
function showNotification(message, type) {
  if (window.UXEnhancements) {
    new UXEnhancements().showNotification(message, type);
  } else {
    alert(message);
  }
}

function showGlobalLoading() {
  if (window.UXEnhancements) {
    new UXEnhancements().showGlobalLoading();
    setTimeout(() => {
      new UXEnhancements().hideGlobalLoading();
    }, 3000);
  }
}

// Initialize AOS
document.addEventListener('DOMContentLoaded', function() {
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 1000,
      once: true
    });
  }
});
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
