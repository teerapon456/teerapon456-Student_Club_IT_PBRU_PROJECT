<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "แก้ไขข้อมูลผู้ใช้งาน | IT SMO";
$pageGroup = 'users';
require_once '../../../includes/admin_header.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
  echo '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;">';
  echo '<h2>ไม่พบ session ผู้ใช้ กรุณาเข้าสู่ระบบใหม่</h2>';
  echo '<a href="/it_smo/pages/public/login.php" class="btn btn-primary mt-3">เข้าสู่ระบบ</a>';
  echo '</div>';
  exit();
}

require_once '../../../api/config/Database.php';
require_once '../../../api/controllers/UserController.php';
require_once '../../../api/models/MajorModal.php';

$db = (new Database())->getConnection();
$userController = new UserController($db);
$majorModel = new MajorModal($db);
$majors = $majorModel->getAllMajors();
$subMajors = $majorModel->getAllSubMajors();

// Get user ID from GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: user_index.php');
    exit();
}
$userId = intval($_GET['id']);
$userData = $userController->getUserById($userId);
if (is_object($userData)) $userData = (array)$userData;
if (!is_array($userData) || empty($userData)) {
  echo '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;">';
  echo '<h2>ไม่พบข้อมูลผู้ใช้ในระบบ</h2>';
  echo '<a href="user_index.php" class="btn btn-primary mt-3">กลับหน้ารายชื่อผู้ใช้</a>';
  echo '</div>';
  exit();
}

// Fetch all roles for dropdown
$roles_stmt = $db->prepare('SELECT role_id, role_name FROM roles ORDER BY role_id ASC');
$roles_stmt->execute();
$roles = $roles_stmt->fetchAll();

// Status options
$statuses = ['เปิดใช้งาน', 'ปิดใช้งาน', 'ระงับการใช้งาน'];

$success = null;
$error = null;

// --- ส่วนบันทึกข้อมูล ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $updateData = [
    'user_id' => $userId,
    'student_id' => trim($_POST['student_id'] ?? $userData['student_id']),
    'first_name' => trim($_POST['first_name'] ?? ''),
    'last_name' => trim($_POST['last_name'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? $userData['phone']),
    'major_id' => $_POST['major_id'] ?? $userData['major_id'],
    'sub_major_id' => $_POST['sub_major_id'] ?? $userData['sub_major_id'],
    'status' => $_POST['status'] ?? $userData['status'],
    'role_id' => $_POST['role_id'] ?? $userData['role_id'],
    'profile_image' => $userData['profile_image'] ?? null
  ];
  if (!empty($_POST['password'])) {
    $updateData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
  }
  // --- Profile image upload ---
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
        $uploadDir = __DIR__ . '/../../../uploads/profile_images/';
        if (!is_dir($uploadDir)) {
          if (!mkdir($uploadDir, 0777, true)) {
            $error = 'ไม่สามารถสร้างโฟลเดอร์สำหรับอัปโหลดได้';
          }
        }
        if (!$error && !is_writable($uploadDir)) {
          $error = 'โฟลเดอร์ปลายทางไม่สามารถเขียนไฟล์ได้: ' . $uploadDir;
        }
        if (!$error) {
          // ลบไฟล์เก่า
          if (!empty($userData['profile_image'])) {
            $oldPath = $uploadDir . basename($userData['profile_image']);
            if (is_file($oldPath)) @unlink($oldPath);
          }
          $newName = 'user_' . $userId . '.' . $ext;
          $targetPath = $uploadDir . $newName;
          if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $updateData['profile_image'] = '/it_smo/uploads/profile_images/' . $newName;
          } else {
            $error = 'ไม่สามารถบันทึกไฟล์รูปภาพได้';
          }
        }
      }
    }
  }
  // --- End robust upload ---
  if (!$error) {
    $result = $userController->handleRequest('PUT', $updateData);
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
          <h3 class="mb-4"><i class="bi bi-person-circle me-2"></i>แก้ไขข้อมูลสมาชิก</h3>
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
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
                    <label for="phone">เบอร์โทรศัพท์</label>
                  </div>
                  <div class="col-md-6 form-floating">
                    <select class="form-select" id="major_id" name="major_id">
                      <option value="">-- เลือกสาขาวิชา --</option>
                      <?php foreach ($majors as $major): ?>
                        <option value="<?= $major['major_id'] ?>" <?= ($userData['major_id'] == $major['major_id']) ? 'selected' : '' ?>><?= htmlspecialchars($major['major_name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <label for="major_id">สาขาวิชา</label>
                  </div>
                  <div class="col-md-6 form-floating">
                    <select class="form-select" id="sub_major_id" name="sub_major_id">
                      <option value="">-- เลือกแขนงวิชา --</option>
                      <?php foreach ($subMajors as $sub): ?>
                        <option value="<?= $sub['sub_major_id'] ?>" <?= ($userData['sub_major_id'] == $sub['sub_major_id']) ? 'selected' : '' ?>><?= htmlspecialchars($sub['sub_major_name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <label for="sub_major_id">แขนงวิชา</label>
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
                    <select class="form-select" id="role_id" name="role_id" required>
                      <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['role_id'] ?>" <?= ($userData['role_id'] == $role['role_id']) ? 'selected' : '' ?>><?= htmlspecialchars($role['role_name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <label for="role_id">บทบาท <span class="text-danger">*</span></label>
                  </div>
                  <div class="col-md-6 form-floating">
                    <select class="form-select" id="status" name="status" required>
                      <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= ($userData['status'] === $s) ? 'selected' : '' ?>><?= $s ?></option>
                      <?php endforeach; ?>
                    </select>
                    <label for="status">สถานะ <span class="text-danger">*</span></label>
                  </div>
                  <div class="col-md-6 form-floating">
                    <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                    <label for="password">รหัสผ่านใหม่ (ถ้าเปลี่ยน)</label>
                  </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                  <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>บันทึกข้อมูล</button>
                  <a href="user_index.php" class="btn btn-outline-secondary px-4"><i class="bi bi-arrow-left me-1"></i>กลับ</a>
                </div>
              </div>
            </div>
          </form>
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
const allSubMajors = <?= json_encode($subMajors) ?>;
function filterSubMajors() {
  const majorId = document.getElementById('major_id').value;
  const subMajorSelect = document.getElementById('sub_major_id');
  // ลบ option เดิม
  subMajorSelect.innerHTML = '<option value=\"\">-- เลือกแขนงวิชา --</option>';
  allSubMajors.forEach(sub => {
    if (!majorId || sub.major_id == majorId) {
      const opt = document.createElement('option');
      opt.value = sub.sub_major_id;
      opt.textContent = sub.sub_major_name;
      if (<?= json_encode($userData['sub_major_id'] ?? '') ?> == sub.sub_major_id) opt.selected = true;
      subMajorSelect.appendChild(opt);
    }
  });
}
document.getElementById('major_id').addEventListener('change', filterSubMajors);
window.addEventListener('DOMContentLoaded', filterSubMajors);
</script>
<?php require_once '../../../includes/admin_footer.php'; ?>
