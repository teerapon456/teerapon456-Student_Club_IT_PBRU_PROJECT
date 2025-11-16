<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ตรวจสอบ session
if (!isset($_SESSION['user_id'])) {
  echo '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;">';
  echo '<h2>ไม่พบ session ผู้ใช้ กรุณาเข้าสู่ระบบใหม่</h2>';
  echo '<a href="/it_smo/pages/public/login.php" class="btn btn-primary mt-3">เข้าสู่ระบบ</a>';
  echo '</div>';
  exit();
}

// เชื่อมต่อฐานข้อมูลและ controller
require_once '../../api/config/Database.php';
require_once '../../api/controllers/UserController.php';

$database = new Database();
$db = $database->getConnection();
$userController = new UserController($db);

$userId = $_SESSION['user_id'];
$userData = $userController->getUserById($userId);
if (is_object($userData)) $userData = (array)$userData;
if (!is_array($userData) || empty($userData)) {
  echo '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;">';
  echo '<h2>ไม่พบข้อมูลผู้ใช้ในระบบ</h2>';
  echo '<a href="/it_smo/pages/public/login.php" class="btn btn-primary mt-3">เข้าสู่ระบบใหม่</a>';
  echo '</div>';
  exit();
}

$pageTitle = 'แก้ไขข้อมูลส่วนตัว | IT SMO';
$pageGroup = 'profile';
require_once '../../includes/admin_header.php';

$success = null;
$error = null;

// --- ส่วนบันทึกข้อมูล ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $updateData = [
    'student_id' => $_POST['student_id'] ?? $userData['student_id'],
    'first_name' => trim($_POST['first_name'] ?? ''),
    'last_name' => trim($_POST['last_name'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'status' => $userData['status'] ?? 'เปิดใช้งาน',
    'role_id' => $userData['role_id'],
    'profile_image' => $userData['profile_image'] ?? null
  ];
  if (!empty($_POST['password'])) {
    $updateData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
  }
  // --- Profile image upload with student_id as filename ---
  if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['profile_image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
      $error = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์ (error code: ' . $file['error'] . ')';
    } else {
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
      if (!in_array($ext, $allowed)) {
        $error = 'อนุญาตเฉพาะไฟล์ jpg, jpeg, png, gif เท่านั้น';
      } elseif ($file['size'] > 5 * 1024 * 1024) {
        $error = 'ขนาดไฟล์ต้องไม่เกิน 5MB';
      } else {
        $studentId = $updateData['student_id'] ?: $userId;
        $uploadDir = __DIR__ . '/../../uploads/profile/' . $userId . '/';
        $relativePath = '/it_smo/uploads/profile/' . $userId . '/';
      if (!is_dir($uploadDir)) {
          if (!mkdir($uploadDir, 0777, true)) {
            $error = 'ไม่สามารถสร้างโฟลเดอร์สำหรับอัปโหลดได้';
          }
        }
        if (!$error && !is_writable($uploadDir)) {
          $error = 'โฟลเดอร์ปลายทางไม่สามารถเขียนไฟล์ได้: ' . $uploadDir;
        }
        if (!$error) {
          // ลบไฟล์เก่าทั้งหมดในโฟลเดอร์ user_id
          foreach (glob($uploadDir . '*') as $oldFile) {
            if (is_file($oldFile)) @unlink($oldFile);
      }
          $newName = $studentId . '.' . $ext;
      $targetPath = $uploadDir . $newName;
          if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $updateData['profile_image'] = $relativePath . $newName;
      } else {
            $error = 'ไม่สามารถบันทึกไฟล์รูปภาพได้';
          }
        }
      }
    }
  }
  // --- End robust upload ---
  if (!$error) {
    $result = $userController->handleRequest('PUT', array_merge(['user_id' => $userId], $updateData));
    if (isset($result['success']) && $result['success']) {
      $success = 'บันทึกข้อมูลสำเร็จ';
      $userData = $userController->getUserById($userId);
      if (is_object($userData)) $userData = (array)$userData;
      if (!is_array($userData)) $userData = [];
    } else {
      $error = $result['message'] ?? 'เกิดข้อผิดพลาดในการบันทึก';
    }
  }
}
$profileImage = $userData['profile_image'] ?? '/it_smo/assets/img/default_profile.png';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
.profile-img-preview {
  width: 160px;
  height: 160px;
  object-fit: cover;
  border-radius: 50%;
  border: 3px solid #e0e0e0;
  background: #f8f9fa;
  margin-bottom: 1rem;
}
.profile-upload-label {
  cursor: pointer;
  color: #0d6efd;
  font-weight: 500;
}
.profile-upload-label:hover {
  text-decoration: underline;
}
</style>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
          <h3 class="mb-4"><i class="bi bi-person-circle me-2"></i>แก้ไขข้อมูลส่วนตัว</h3>
  <?php if ($success): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($success) ?></div>
  <?php elseif ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
          <form method="post" enctype="multipart/form-data" autocomplete="off">
            <div class="row g-4 align-items-start">
              <div class="col-md-4 text-center">
                <img id="profilePreview" src="<?= htmlspecialchars($profileImage) ?>" class="profile-img-preview shadow-sm" alt="Profile Image">
                <div class="mt-2">
                  <label class="profile-upload-label">
                    <i class="bi bi-camera me-1"></i> เปลี่ยนรูปโปรไฟล์
                    <input type="file" name="profile_image" accept="image/*" class="d-none" onchange="previewImage(event)">
                  </label>
                </div>
                <div class="text-muted small mt-2">รองรับ JPG, PNG, GIF | สูงสุด 5MB</div>
              </div>
              <div class="col-md-8">
                <div class="row g-3">
                  <div class="col-md-6 form-floating">
                    <input type="text" class="form-control" id="student_id" name="student_id" value="<?= htmlspecialchars($userData['student_id'] ?? '') ?>">
                    <label for="student_id">รหัสนักศึกษา/เจ้าหน้าที่</label>
              </div>
                  <div class="col-md-6 form-floating">
                    <input type="text" class="form-control" id="role_name" value="<?= htmlspecialchars($userData['role_name'] ?? '-') ?>" disabled>
                    <label for="role_name">บทบาท</label>
            </div>
                  <div class="col-md-6 form-floating">
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($userData['first_name'] ?? '') ?>" required>
                    <label for="first_name">ชื่อ <span class="text-danger">*</span></label>
              </div>
                  <div class="col-md-6 form-floating">
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($userData['last_name'] ?? '') ?>" required>
                    <label for="last_name">นามสกุล <span class="text-danger">*</span></label>
              </div>
                  <div class="col-md-6 form-floating">
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required>
                    <label for="email">อีเมล <span class="text-danger">*</span></label>
            </div>
                  <div class="col-md-6 form-floating">
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
                    <label for="phone">เบอร์โทรศัพท์</label>
              </div>
                  <div class="col-md-6 form-floating">
                    <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                    <label for="password">รหัสผ่านใหม่ (ถ้าเปลี่ยน)</label>
              </div>
            </div>
                <div class="d-flex gap-2 mt-4">
                  <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>บันทึกข้อมูล</button>
                  <button type="reset" class="btn btn-outline-secondary px-4"><i class="bi bi-arrow-counterclockwise me-1"></i>รีเซ็ต</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="mb-2"><i class="bi bi-info-circle me-1"></i> ข้อมูลบัญชี</h6>
              <div class="mb-2"><span class="text-muted">สถานะ:</span> <span class="badge bg-success"><?= htmlspecialchars($userData['status'] ?? 'เปิดใช้งาน') ?></span></div>
              <div class="mb-2"><span class="text-muted">วันที่สมัคร:</span> <?= date('d/m/Y', strtotime($userData['created_at'] ?? 'now')) ?></div>
              <div><span class="text-muted">อัปเดตล่าสุด:</span> <?= date('d/m/Y H:i', strtotime($userData['updated_at'] ?? 'now')) ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
function previewImage(event) {
  const input = event.target;
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('profilePreview').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
<?php require_once '../../includes/admin_footer.php'; ?>