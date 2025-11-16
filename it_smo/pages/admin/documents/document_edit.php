<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$pageTitle = "แก้ไขเอกสาร | IT SMO";
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';


$database = new Database();
$db = $database->getConnection();

// ดึงข้อมูลหมวดหมู่
function getCategories($db)
{
  $stmt = $db->query("SELECT category_id, category_name FROM document_categories ORDER BY category_name");
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ดึงข้อมูลเอกสารเดิม
$docId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $db->prepare("SELECT * FROM documents WHERE document_id = ?");
$stmt->execute([$docId]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doc) {
  echo '<div class="alert alert-danger m-4">ไม่พบข้อมูลเอกสาร</div>';
  exit;
}

$categories = getCategories($db);
$errors = [];
$successMessage = '';
$errorMessage = '';

$allowedRoles = ['ผู้ดูแลระบบ', 'นายกสโมสรนักศึกษา','อาจารย์ที่ปรึกษา','รองนายกสโมสรนักศึกษา','เลขานุการสโมสรนักศึกษา'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
  header('Location: /it_smo/pages/error/403.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'] ?? '';
  $description = $_POST['description'] ?? '';
  $category_id = $_POST['category_id'] ?? '';
  $status = $_POST['status'] ?? 'ร่าง';
  $access_level = $_POST['access_level'] ?? 'ภายใน';
  $old_file = $doc['file_path'];
  $file_path = $old_file;
  $file_type = $doc['file_type'];
  $file_size = $doc['file_size'];

  if (empty($title)) $errors[] = "กรุณากรอกชื่อเอกสาร";
  if (empty($category_id)) $errors[] = "กรุณาเลือกหมวดหมู่";

  // ถ้ามีอัปโหลดไฟล์ใหม่
  if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $maxSize = 10 * 1024 * 1024;
    if (!in_array($_FILES['document']['type'], $allowedTypes)) {
      $errors[] = "ประเภทไฟล์ไม่ถูกต้อง (รองรับ PDF, DOC, DOCX)";
    } elseif ($_FILES['document']['size'] > $maxSize) {
      $errors[] = "ขนาดไฟล์เกิน 10MB";
    } else {
      $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
      $newFileName = uniqid('doc_', true) . '.' . $ext;
      $uploadDir = '../../../uploads/documents/';
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
      $targetPath = $uploadDir . $newFileName;
      if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
        // ลบไฟล์เก่า
        if ($old_file && file_exists($uploadDir . $old_file)) {
          @unlink($uploadDir . $old_file);
        }
        $file_path = $newFileName;
        $file_type = $_FILES['document']['type'];
        $file_size = $_FILES['document']['size'];
      } else {
        $errors[] = "ไม่สามารถอัปโหลดไฟล์ใหม่ได้";
      }
    }
  }

  if (empty($errors)) {
    $stmt = $db->prepare("UPDATE documents SET title=?, description=?, category_id=?, status=?, access_level=?, file_path=?, file_type=?, file_size=?, updated_at=NOW() WHERE document_id=?");
    $result = $stmt->execute([
      $title,
      $description,
      $category_id,
      $status,
      $access_level,
      $file_path,
      $file_type,
      $file_size,
      $docId
    ]);
  }
}

?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
      <div class="card upload-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-edit me-2"></i>แก้ไขเอกสาร</h2>
            <a href="document_index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> กลับ</a>
          </div>
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger rounded-3 shadow-sm">
              <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul>
            </div>
          <?php endif; ?>
          <?php if ($errorMessage): ?><div class="alert alert-danger rounded-3 shadow-sm"> <?= $errorMessage ?> </div><?php endif; ?>
          <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="off">
            <div class="mb-3">
              <label class="form-label">ชื่อเอกสาร <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($_POST['title'] ?? $doc['title']) ?>" required maxlength="255">
              <div class="invalid-feedback">กรุณากรอกชื่อเอกสาร</div>
            </div>
            <div class="mb-3">
              <label class="form-label">คำอธิบาย</label>
              <textarea class="form-control" name="description" rows="3" maxlength="1000"><?= htmlspecialchars($_POST['description'] ?? $doc['description']) ?></textarea>
            </div>
            <div class="row mb-3">
              <div class="col-md-6 mb-3 mb-md-0">
                <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                <select class="form-select" name="category_id" required>
                  <option value="">เลือกหมวดหมู่</option>
                  <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['category_id'] ?>" <?= (($_POST['category_id'] ?? $doc['category_id']) == $category['category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($category['category_name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">กรุณาเลือกหมวดหมู่</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">สถานะ</label>
                <select class="form-select" name="status">
                  <option value="ร่าง" <?= (($_POST['status'] ?? $doc['status']) == 'ร่าง') ? 'selected' : '' ?>>ร่าง</option>
                  <option value="เผยแพร่" <?= (($_POST['status'] ?? $doc['status']) == 'เผยแพร่') ? 'selected' : '' ?>>เผยแพร่</option>
                  <option value="ยกเลิก" <?= (($_POST['status'] ?? $doc['status']) == 'ยกเลิก') ? 'selected' : '' ?>>ยกเลิก</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">ระดับการเข้าถึง</label>
              <select class="form-select" name="access_level">
                <option value="สาธารณะ" <?= (($_POST['access_level'] ?? $doc['access_level']) == 'สาธารณะ') ? 'selected' : '' ?>>สาธารณะ</option>
                <option value="ภายใน" <?= (($_POST['access_level'] ?? $doc['access_level']) == 'ภายใน') ? 'selected' : '' ?>>ภายใน</option>
                <option value="ลับ" <?= (($_POST['access_level'] ?? $doc['access_level']) == 'ลับ') ? 'selected' : '' ?>>ลับ</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">ไฟล์เอกสาร (ถ้าไม่เลือกจะใช้ไฟล์เดิม)</label>
              <input type="file" class="form-control" name="document">
              <?php if ($doc['file_path']): ?>
                <div class="form-text">
                  ไฟล์เดิม: 
                  <a href="../../../uploads/documents/<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">ดู/ดาวน์โหลด</a>
                  <a href="../../../uploads/documents/<?= htmlspecialchars($doc['file_path']) ?>" download class="btn btn-sm btn-outline-primary ms-2"><i class="fas fa-download"></i> ดาวน์โหลดไฟล์</a>
                </div>
              <?php endif; ?>
            </div>
            <div class="text-end mt-3">
              <button type="reset" class="btn btn-secondary me-2">ล้างข้อมูล</button>
              <button type="submit" class="btn btn-primary px-4">บันทึกการแก้ไข</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  (function() {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function(form) {
      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
  })()
</script>
<?php include '../../../includes/admin_footer.php'; ?>