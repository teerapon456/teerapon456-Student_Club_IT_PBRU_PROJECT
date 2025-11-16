<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "เพิ่มผู้ใช้ใหม่ | IT SMO";
$pageGroup = 'users'; 

require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';
require_once '../../../api/controllers/UserController.php';
require_once '../../../api/models/MajorModal.php';
require_once '../../../api/models/RoleModal.php';

// เรียกใช้ Auth ผ่าน getInstance
$auth = Auth::getInstance();
$user = $auth->getCurrentUser();

$allowedRoles = ['ผู้ดูแลระบบ', 'ประธานสโมสร','เจ้าหน้าที่'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
  header('Location: /it_smo/pages/error/403.php');
  exit();
}



// Initialize database connection and controllers
$database = new Database();
$db = $database->getConnection();
$userController = new UserController($db);
$majorModel = new MajorModal($db);
$roleModel = new RoleModal($db);

// Get roles and majors for dropdowns
$rolesResult = $userController->handleRequest('GET', ['type' => 'roles']);
$roles = (is_array($rolesResult) && isset($rolesResult['data'])) ? $rolesResult['data'] : [];
$majors = $majorModel->getAllMajors();

$successMessage = null;
$errorMessage = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Collect and sanitize data
  $userData = [
    'student_id' => $_POST['student_id'] ?? null,
    'password' => $_POST['password'],
    'email' => $_POST['email'],
    'first_name' => $_POST['first_name'],
    'last_name' => $_POST['last_name'],
    'phone' => $_POST['phone'] ?? null,
    'role_id' => $_POST['role_id'],
    'major_id' => !empty($_POST['major_id']) ? $_POST['major_id'] : null,
    'sub_major_id' => !empty($_POST['sub_major_id']) ? $_POST['sub_major_id'] : null,
    'status' => $_POST['status']
  ];

  // Handle profile image upload
  if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../../../uploads/profiles/';
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    $fileName = uniqid() . '_' . basename($_FILES['profile_image']['name']);
    $uploadFile = $uploadDir . $fileName;
    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
      $userData['profile_image'] = '/it_smo/uploads/profiles/' . $fileName;
    }
  }

  $result = $userController->handleRequest('POST', $userData);

}

?>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-plus me-2"></i>เพิ่มผู้ใช้ใหม่</h2>
            <a href="user_index.php" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-1"></i> กลับ
            </a>
          </div>

          <?php if ($successMessage): ?>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
              Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: '<?= $successMessage ?>',
                confirmButtonText: 'ตกลง'
              });
            </script>
          <?php endif; ?>

          <?php if ($errorMessage): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?= $errorMessage ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <!-- Form fields -->
            <div class="row g-3">
              <!-- Student ID -->
              <div class="col-md-6">
                <label for="student_id" class="form-label">รหัสนักศึกษา <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="student_id" name="student_id"
                  value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" required pattern="[0-9]{9}"
                  maxlength="9">
                <div class="invalid-feedback">กรุณากรอกรหัสนักศึกษา 9 หลัก</div>
              </div>

              <!-- Email -->
              <div class="col-md-6">
                <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email"
                  value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <div class="invalid-feedback">กรุณากรอกอีเมลที่ถูกต้อง</div>
              </div>

              <!-- Password -->
              <div class="col-md-6">
                <label for="password" class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                <div class="invalid-feedback">รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร</div>
              </div>

              <!-- Confirm Password -->
              <div class="col-md-6">
                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน <span
                    class="text-danger">*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <div class="invalid-feedback">รหัสผ่านไม่ตรงกัน</div>
              </div>

              <!-- First Name -->
              <div class="col-md-6">
                <label for="first_name" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="first_name" name="first_name"
                  value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                <div class="invalid-feedback">กรุณากรอกชื่อ</div>
              </div>

              <!-- Last Name -->
              <div class="col-md-6">
                <label for="last_name" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="last_name" name="last_name"
                  value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                <div class="invalid-feedback">กรุณากรอกนามสกุล</div>
              </div>

              <!-- Phone -->
              <div class="col-md-6">
                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                <input type="tel" class="form-control" id="phone" name="phone"
                  value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" pattern="[0-9]{10}">
                <div class="invalid-feedback">กรุณากรอกเบอร์โทรศัพท์ 10 หลัก</div>
              </div>

              <!-- Role -->
              <div class="col-md-6">
                <label for="role_id" class="form-label">บทบาท <span class="text-danger">*</span></label>
                <select class="form-select" id="role_id" name="role_id" required>
                  <option value="">เลือกบทบาท</option>
                  <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['role_id'] ?>"
                      <?= (isset($_POST['role_id']) && $_POST['role_id'] == $role['role_id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($role['role_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">กรุณาเลือกบทบาท</div>
              </div>

              <!-- Major -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="major_id" class="form-label">สาขาวิชา</label>
                  <select class="form-select" id="major_id" name="major_id">
                    <option value="">-- เลือกสาขาวิชา --</option>
                    <?php foreach ($majors as $major): ?>
                    <option value="<?= $major['major_id'] ?>"><?= htmlspecialchars($major['major_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="sub_major_id" class="form-label">แขนงวิชา</label>
                  <select class="form-select" id="sub_major_id" name="sub_major_id">
                    <option value="">-- เลือกแขนงวิชา --</option>
                    <!-- Options will be loaded by JavaScript -->
                  </select>
                </div>
              </div>

              <!-- Profile Image -->
              <div class="col-md-6">
                <label for="profile_image" class="form-label">รูปโปรไฟล์</label>
                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                <div class="form-text">รองรับไฟล์รูปภาพขนาดไม่เกิน 2MB</div>
              </div>

              <!-- Status -->
              <div class="col-md-6">
                <label for="status" class="form-label">สถานะ</label>
                <select class="form-select" id="status" name="status" required>
                  <option value="">เลือกสถานะ</option>
                  <option value="เปิดใช้งาน" <?= (isset($_POST['status']) && $_POST['status'] === 'เปิดใช้งาน') ? 'selected' : '' ?>>เปิดใช้งาน</option>
                  <option value="ปิดใช้งาน" <?= (isset($_POST['status']) && $_POST['status'] === 'ปิดใช้งาน') ? 'selected' : '' ?>>ปิดใช้งาน</option>
                </select>
                <div class="invalid-feedback">กรุณาเลือกสถานะ</div>
              </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
              <a href="user_index.php" class="btn btn-secondary">ยกเลิก</a>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> บันทึก
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Form validation
  (function() {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function(form) {
      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        // Check password match
        var password = document.getElementById('password')
        var confirm = document.getElementById('confirm_password')
        if (password.value !== confirm.value) {
          confirm.setCustomValidity('รหัสผ่านไม่ตรงกัน')
          event.preventDefault()
          event.stopPropagation()
        } else {
          confirm.setCustomValidity('')
        }

        form.classList.add('was-validated')
      }, false)
    })
  })()

  // Filter sub_majors by selected major
  document.addEventListener('DOMContentLoaded', function() {
    const majorSelect = document.getElementById('major_id');
    const subMajorSelect = document.getElementById('sub_major_id');

    majorSelect.addEventListener('change', function() {
      const majorId = this.value;
      subMajorSelect.innerHTML = '<option value="">-- กำลังโหลด --</option>';

      if (!majorId) {
        subMajorSelect.innerHTML = '<option value="">-- เลือกแขนงวิชา --</option>';
        return;
      }

      fetch(`/it_smo/api/endpoints/majors.php?major_id=${majorId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          subMajorSelect.innerHTML = '<option value="">-- เลือกแขนงวิชา --</option>';
          if (data.success && data.data && data.data.length > 0) {
            data.data.forEach(subMajor => {
              const option = document.createElement('option');
              option.value = subMajor.sub_major_id;
              option.textContent = subMajor.sub_major_name;
              subMajorSelect.appendChild(option);
            });
          } else {
             subMajorSelect.innerHTML = '<option value="">-- ไม่พบแขนงวิชา --</option>';
          }
        })
        .catch(error => {
          console.error('Error fetching sub-majors:', error);
          subMajorSelect.innerHTML = '<option value="">-- เกิดข้อผิดพลาด --</option>';
        });
    });
  });
</script>

<?php include '../../../includes/admin_footer.php'; ?>