<?php
$pageTitle = "ตั้งค่าทั่วไป | IT SMO";
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';

$auth = new Auth();
$user = $auth->getCurrentUser();


// ฟังก์ชันสำหรับดึงข้อมูลการตั้งค่า
function getSettings()
{
  // TODO: เชื่อมต่อกับฐานข้อมูลและดึงข้อมูลการตั้งค่า
  return [
    'site_name' => 'IT SMO',
    'site_description' => 'ระบบจัดการสโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ',
    'contact_email' => 'it_smo@example.com',
    'contact_phone' => '02-XXX-XXXX',
    'facebook_url' => 'https://facebook.com/it_smo',
    'instagram_url' => 'https://instagram.com/it_smo',
    'line_url' => 'https://line.me/it_smo',
    'maintenance_mode' => false,
    'allow_registration' => true,
    'max_file_size' => 10, // MB
    'allowed_file_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
    'session_timeout' => 30, // นาที
    'items_per_page' => 10
  ];
}

// ฟังก์ชันสำหรับบันทึกการตั้งค่า
function saveSettings($settings)
{
  // TODO: เชื่อมต่อกับฐานข้อมูลและบันทึกการตั้งค่า
  return true;
}

// จัดการการส่งฟอร์ม
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $settings = [
    'site_name' => $_POST['site_name'] ?? '',
    'site_description' => $_POST['site_description'] ?? '',
    'contact_email' => $_POST['contact_email'] ?? '',
    'contact_phone' => $_POST['contact_phone'] ?? '',
    'facebook_url' => $_POST['facebook_url'] ?? '',
    'instagram_url' => $_POST['instagram_url'] ?? '',
    'line_url' => $_POST['line_url'] ?? '',
    'maintenance_mode' => isset($_POST['maintenance_mode']),
    'allow_registration' => isset($_POST['allow_registration']),
    'max_file_size' => (int)($_POST['max_file_size'] ?? 10),
    'allowed_file_types' => explode(',', $_POST['allowed_file_types'] ?? ''),
    'session_timeout' => (int)($_POST['session_timeout'] ?? 30),
    'items_per_page' => (int)($_POST['items_per_page'] ?? 10)
  ];

  if (saveSettings($settings)) {
    $message = 'บันทึกการตั้งค่าสำเร็จ';
    $messageType = 'success';
  } else {
    $message = 'เกิดข้อผิดพลาดในการบันทึกการตั้งค่า';
    $messageType = 'danger';
  }
}

$settings = getSettings();
?>

<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12 col-md-8 mx-auto">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>ตั้งค่าทั่วไป</h2>
      </div>

      <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
          <?= $message ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form method="POST" class="needs-validation" novalidate>
        <!-- ข้อมูลพื้นฐาน -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">ข้อมูลพื้นฐาน</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">ชื่อเว็บไซต์</label>
                <input type="text" class="form-control" name="site_name"
                  value="<?= htmlspecialchars($settings['site_name']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">คำอธิบายเว็บไซต์</label>
                <input type="text" class="form-control" name="site_description"
                  value="<?= htmlspecialchars($settings['site_description']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">อีเมลติดต่อ</label>
                <input type="email" class="form-control" name="contact_email"
                  value="<?= htmlspecialchars($settings['contact_email']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">เบอร์โทรศัพท์ติดต่อ</label>
                <input type="tel" class="form-control" name="contact_phone"
                  value="<?= htmlspecialchars($settings['contact_phone']) ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- Social Media -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Social Media</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Facebook URL</label>
                <input type="url" class="form-control" name="facebook_url"
                  value="<?= htmlspecialchars($settings['facebook_url']) ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Instagram URL</label>
                <input type="url" class="form-control" name="instagram_url"
                  value="<?= htmlspecialchars($settings['instagram_url']) ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Line URL</label>
                <input type="url" class="form-control" name="line_url"
                  value="<?= htmlspecialchars($settings['line_url']) ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- ระบบ -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">ตั้งค่าระบบ</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="maintenance_mode"
                    <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                  <label class="form-check-label">โหมดบำรุงรักษา</label>
                </div>
                <small class="text-muted">เปิดใช้งานเมื่อต้องการบำรุงรักษาระบบ</small>
              </div>
              <div class="col-md-6">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="allow_registration"
                    <?= $settings['allow_registration'] ? 'checked' : '' ?>>
                  <label class="form-check-label">เปิดใช้งานการลงทะเบียน</label>
                </div>
                <small class="text-muted">อนุญาตให้ผู้ใช้ใหม่ลงทะเบียนได้</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">ขนาดไฟล์สูงสุด (MB)</label>
                <input type="number" class="form-control" name="max_file_size"
                  value="<?= $settings['max_file_size'] ?>" min="1" max="100" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">ประเภทไฟล์ที่อนุญาต</label>
                <input type="text" class="form-control" name="allowed_file_types"
                  value="<?= htmlspecialchars(implode(',', $settings['allowed_file_types'])) ?>" required>
                <small class="text-muted">คั่นด้วยเครื่องหมายจุลภาค (,)</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">เวลาหมดอายุของเซสชัน (นาที)</label>
                <input type="number" class="form-control" name="session_timeout"
                  value="<?= $settings['session_timeout'] ?>" min="5" max="1440" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">จำนวนรายการต่อหน้า</label>
                <input type="number" class="form-control" name="items_per_page"
                  value="<?= $settings['items_per_page'] ?>" min="5" max="100" required>
              </div>
            </div>
          </div>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> บันทึกการตั้งค่า
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Form validation
  (function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();
</script>

<?php include '../../../includes/admin_footer.php'; ?>