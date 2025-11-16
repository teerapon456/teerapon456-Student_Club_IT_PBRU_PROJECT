<?php

$pageTitle = "เพิ่มเอกสารใหม่ | IT SMO";

require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';

// ฟังก์ชันสำหรับดึงข้อมูลหมวดหมู่
function getCategories()
{
  $database = new Database();
  $db = $database->getConnection();
  $stmt = $db->query("SELECT category_id, category_name FROM document_categories ORDER BY category_name");
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ฟังก์ชันสำหรับอัปโหลดเอกสาร
function uploadDocument($documentData, $file)
{
  $database = new Database();
  $db = $database->getConnection();

  // ตรวจสอบและย้ายไฟล์
  $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
  $maxSize = 10 * 1024 * 1024; // 10MB
  if (!in_array($file['type'], $allowedTypes)) {
    return "ประเภทไฟล์ไม่ถูกต้อง (รองรับ PDF, DOC, DOCX)";
  }
  if ($file['size'] > $maxSize) {
    return "ขนาดไฟล์เกิน 10MB";
  }
  $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

  // --- ดึง category_name จาก category_id ---
  $stmt = $db->prepare("SELECT category_name FROM document_categories WHERE category_id = ?");
  $stmt->execute([$documentData['category_id']]);
  $catRow = $stmt->fetch(PDO::FETCH_ASSOC);
  $categoryName = $catRow ? preg_replace('/[\\\\\/\:\*\?\"\<\>\|]/', '', $catRow['category_name']) : 'อื่นๆ';

  // --- ใช้ document_year จากฟอร์ม ---
  $year = isset($documentData['document_year']) && preg_match('/^[0-9]{4}$/', $documentData['document_year']) ? $documentData['document_year'] : date('Y');

  // --- สร้าง path โฟลเดอร์ ---
  $uploadDir = '../../../uploads/documents/' . $year . '/' . $categoryName . '/';
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }
  // --- ตรวจสอบเลขที่เอกสารซ้ำในปีและหมวดหมู่เดียวกัน ---
  $stmt = $db->prepare("SELECT COUNT(*) FROM documents WHERE document_number = ? AND category_id = ? AND document_year = ?");
  $stmt->execute([$documentData['document_number'], $documentData['category_id'], $year]);
  if ($stmt->fetchColumn() > 0) {
    return "เลขที่เอกสารนี้ถูกใช้แล้วในหมวดหมู่และปีเดียวกัน กรุณาเปลี่ยนเลขที่เอกสาร";
  }
  // --- ตรวจสอบชื่อเอกสารซ้ำในปีและหมวดหมู่เดียวกัน ---
  $stmt = $db->prepare("SELECT COUNT(*) FROM documents WHERE title = ? AND category_id = ? AND document_year = ?");
  $stmt->execute([$documentData['title'], $documentData['category_id'], $year]);
  if ($stmt->fetchColumn() > 0) {
    return "ชื่อเอกสารนี้ถูกใช้แล้วในหมวดหมู่และปีเดียวกัน กรุณาเปลี่ยนชื่อเอกสาร";
  }
  // --- ตรวจสอบชื่อไฟล์ซ้ำในโฟลเดอร์ ---
  $existingFilePath = $uploadDir . $file['name'];
  if (file_exists($existingFilePath)) {
    return "มีไฟล์ชื่อเดียวกันนี้อยู่แล้วในระบบ กรุณาเปลี่ยนชื่อไฟล์ก่อนอัปโหลด";
  }
  // --- ตรวจสอบชื่อไฟล์ซ้ำในฐานข้อมูล (file_path) ---
  $webPathCheck = '/it_smo/uploads/documents/' . $year . '/' . $categoryName . '/' . $file['name'];
  $stmt = $db->prepare("SELECT COUNT(*) FROM documents WHERE file_path = ?");
  $stmt->execute([$webPathCheck]);
  if ($stmt->fetchColumn() > 0) {
    return "มีไฟล์ชื่อเดียวกันนี้ถูกบันทึกไว้ในระบบแล้ว กรุณาเปลี่ยนชื่อไฟล์ก่อนอัปโหลด";
  }
  $newFileName = uniqid('doc_', true) . '.' . $ext;
  $targetPath = $uploadDir . $newFileName;
  if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    return "ไม่สามารถอัปโหลดไฟล์ได้";
  }
  $webPath = '/it_smo/uploads/documents/' . $year . '/' . $categoryName . '/' . $newFileName;

  // --- บันทึกข้อมูลลงฐานข้อมูล (ตรง schema จริง) ---
  $stmt = $db->prepare("INSERT INTO documents (document_number, title, description, category_id, status, access_level, file_path, file_type, file_size, uploaded_by, publish_date, document_year, keywords, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
  $result = $stmt->execute([
    $documentData['document_number'],
    $documentData['title'],
    $documentData['description'],
    $documentData['category_id'],
    $documentData['status'],
    $documentData['access_level'],
    $webPath,
    $file['type'],
    $file['size'],
    $_SESSION['user_id'],
    $documentData['publish_date'],
    $year,
    $documentData['keywords'] ?? null
  ]);
  return $result ? true : "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
}

// จัดการการอัปโหลดเอกสาร
$documentData = [
  'document_number' => '',
  'title' => '',
  'description' => '',
  'category_id' => '',
  'status' => 'ร่าง',
  'access_level' => 'ภายใน',
  'document_year' => date('Y'),
  'publish_date' => null,
  'keywords' => null
];
$errors = [];
$uploadSuccess = false; // เพิ่มตัวแปรเพื่อตรวจสอบสถานะการอัปโหลด
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $documentData = [
    'document_number' => $_POST['document_number'] ?? '',
    'title' => $_POST['title'] ?? '',
    'description' => $_POST['description'] ?? '',
    'category_id' => $_POST['category_id'] ?? '',
    'status' => $_POST['status'] ?? 'ร่าง',
    'access_level' => $_POST['access_level'] ?? 'ภายใน',
    'document_year' => $_POST['document_year'] ?? date('Y'),
    // publish_date รับเป็น dd/mm/yyyy (ไทย) แล้วแปลงเป็น yyyy-mm-dd (สากล) ก่อนบันทึก
    'publish_date' => isset($_POST['publish_date']) && $_POST['publish_date'] ?
      thaiDateToIso($_POST['publish_date']) : null,
    'keywords' => $_POST['keywords'] ?? null
  ];

  // ตรวจสอบข้อมูล
  if (empty($documentData['document_number']))
    $errors[] = "กรุณากรอกเลขที่เอกสาร";
  if (empty($documentData['title']))
    $errors[] = "กรุณากรอกชื่อเอกสาร";
  if (empty($documentData['category_id']))
    $errors[] = "กรุณาเลือกหมวดหมู่";
  if (!isset($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE)
    $errors[] = "กรุณาเลือกไฟล์เอกสาร";

  if (empty($errors)) {
    $result = uploadDocument($documentData, $_FILES['document']);
    if ($result === true) {
      $uploadSuccess = true;
    } else {
      $errorMessage = $result;
    }
  }
}

$categories = getCategories();
require_once '../../../includes/admin_header.php';
?>
<link rel="stylesheet" href="../../../assets/css/modern-admin.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/masasron/jquery-ui-datepicker-thai@master/jquery.ui.datepicker-th.js"></script>
<div class="container-fluid">
  <div class="page-header mb-4">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h1 class="page-title mb-1">เพิ่มเอกสารใหม่</h1>
        <p class="text-muted mb-0">กรอกข้อมูลและอัปโหลดไฟล์เอกสารที่ต้องการ</p>
      </div>
      <div class="d-flex gap-2">
        <?php
        $dashboardUrls = [
          'ผู้ดูแลระบบ' => '../dashboard/admin.php',
          'นายกสโมสรนักศึกษา' => '../dashboard/president.php',
          'อาจารย์ที่ปรึกษา' => '../dashboard/advisor.php',
          'รองนายกสโมสรนักศึกษา' => '../dashboard/vice_president.php',
          'เลขานุการสโมสรนักศึกษา' => '../dashboard/secretary.php',
          'กรรมการสโมสรนักศึกษา' => '../dashboard/committee.php',
          'อนุกรรมการสโมสรนักศึกษา' => '../dashboard/subcommittee.php',
        ];
        $dashboardUrl = isset($dashboardUrls[$_SESSION['user_role']]) ? $dashboardUrls[$_SESSION['user_role']] : '../dashboard/index.php';
        ?>
        <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="btn btn-outline-primary">
          <i class="fas fa-arrow-left"></i> กลับ
        </a>
      </div>
    </div>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i>
      <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?= $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php elseif (isset($errorMessage)): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i>
      <?= $errorMessage ?>
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="fas fa-file-upload me-2"></i>
            ข้อมูลเอกสาร
          </h5>
        </div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data" autocomplete="off" class="needs-validation" novalidate>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">เลขที่เอกสาร <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="document_number" value="<?= htmlspecialchars($documentData['document_number'] ?? '') ?>" required maxlength="20">
                <div class="invalid-feedback">กรุณากรอกเลขที่เอกสาร</div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">ชื่อเอกสาร <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($documentData['title'] ?? '') ?>" required maxlength="255">
                <div class="invalid-feedback">กรุณากรอกชื่อเอกสาร</div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                <select class="form-select" name="category_id" required>
                  <option value="">เลือกหมวดหมู่</option>
                  <?php foreach ($categories as $category): ?>
                  <option value="<?= $category['category_id'] ?>" <?= isset($documentData['category_id']) && $documentData['category_id'] == $category['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['category_name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">กรุณาเลือกหมวดหมู่</div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">ปีเอกสาร <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="document_year" value="<?= htmlspecialchars($documentData['document_year'] ?? date('Y')) ?>" required maxlength="4" pattern="[0-9]{4}">
                <div class="invalid-feedback">กรุณากรอกปี (ตัวเลข 4 หลัก)</div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">วันที่เผยแพร่ (วัน/เดือน/ปี พ.ศ.)</label>
                <input type="text" class="form-control" id="publish_date" name="publish_date" value="<?= isset($documentData['publish_date']) && $documentData['publish_date'] ? isoToThaiDate($documentData['publish_date']) : '' ?>" placeholder="เช่น 17/07/2568" autocomplete="off">
                <div class="form-text">กรุณาเลือกวัน/เดือน/ปี พ.ศ. จากปฏิทิน</div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">คำอธิบาย</label>
              <textarea class="form-control" name="description" rows="3" maxlength="1000"><?= htmlspecialchars($documentData['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">คำสำคัญ (Keywords)</label>
              <input type="text" class="form-control" name="keywords" maxlength="255" value="<?= htmlspecialchars($documentData['keywords'] ?? '') ?>" placeholder="เช่น: ประชุม, ระเบียบ, ...">
              <div class="form-text">คั่นแต่ละคำด้วยเครื่องหมายจุลภาค (,)</div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">สถานะ</label>
                <select class="form-select" name="status">
                  <option value="ร่าง" <?= (!isset($documentData['status']) || $documentData['status'] === 'ร่าง') ? 'selected' : '' ?>>ร่าง</option>
                  <option value="เผยแพร่" <?= isset($documentData['status']) && $documentData['status'] === 'เผยแพร่' ? 'selected' : '' ?>>เผยแพร่</option>
                  <option value="ยกเลิก" <?= isset($documentData['status']) && $documentData['status'] === 'ยกเลิก' ? 'selected' : '' ?>>ยกเลิก</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">ระดับการเข้าถึง</label>
                <select class="form-select" name="access_level">
                  <option value="สาธารณะ" <?= (isset($documentData['access_level']) && $documentData['access_level'] === 'สาธารณะ') ? 'selected' : '' ?>>สาธารณะ</option>
                  <option value="ภายใน" <?= (!isset($documentData['access_level']) || $documentData['access_level'] === 'ภายใน') ? 'selected' : '' ?>>ภายใน</option>
                  <option value="ลับ" <?= (isset($documentData['access_level']) && $documentData['access_level'] === 'ลับ') ? 'selected' : '' ?>>ลับ</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">ไฟล์เอกสาร <span class="text-danger">*</span></label>
              <input type="file" class="form-control" name="document" required accept=".pdf,.doc,.docx">
              <div class="form-text">รองรับไฟล์ PDF, DOC, DOCX ขนาดไม่เกิน 10MB</div>
              <div class="invalid-feedback">กรุณาเลือกไฟล์เอกสาร</div>
            </div>
            <div class="d-flex gap-2 mt-4">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload me-2"></i>
                เพิ่มเอกสาร
              </button>
              <button type="reset" class="btn btn-outline-secondary">
                <i class="fas fa-undo me-2"></i>
                รีเซ็ต
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="fas fa-info-circle me-2"></i>
            คำแนะนำการอัปโหลด
          </h5>
        </div>
        <div class="card-body">
          <ul class="mb-2">
            <li>รองรับไฟล์ PDF, DOC, DOCX เท่านั้น</li>
            <li>ขนาดไฟล์สูงสุด 10MB</li>
            <li>กรอกข้อมูลให้ครบถ้วนก่อนอัปโหลด</li>
            <li>เลือกหมวดหมู่และระดับการเข้าถึงให้เหมาะสม</li>
          </ul>
          <div class="alert alert-info small mb-0">
            <i class="fas fa-lightbulb me-1"></i>
            หากพบปัญหาในการอัปโหลด กรุณาติดต่อผู้ดูแลระบบ
          </div>
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
      form.classList.add('was-validated')
    }, false)
  })
})()

// Datepicker ปีไทย
$.datepicker.regional['th'] = {
    closeText: 'ปิด',
    prevText: '< ย้อน',
    nextText: 'ถัดไป >',
    currentText: 'วันนี้',
    monthNames: ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
    'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],
    monthNamesShort: ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.',
    'ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'],
    dayNames: ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัส','ศุกร์','เสาร์'],
    dayNamesShort: ['อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'],
    dayNamesMin: ['อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'],
    weekHeader: 'Wk',
    dateFormat: 'dd/mm/yy',
    firstDay: 0,
    isBuddhist: true,
    defaultDate: null,
    yearRange: "c-50:c+10",
    showAnim: "fadeIn"
};
$.datepicker.setDefaults($.datepicker.regional['th']);
$("#publish_date").datepicker({
    changeMonth: true,
    changeYear: true,
    yearRange: "c-50:c+10",
    isBuddhist: true
});
$("#publish_date").on("change", function() {
  var val = $(this).val();
  // รูปแบบ dd/mm/yyyy
  var parts = val.split('/');
  if (parts.length === 3) {
    var year_th = parseInt(parts[2], 10);
    if (!isNaN(year_th)) {
      $("input[name='document_year']").val(year_th);
    }
  }
});
</script>
<?php include '../../../includes/admin_footer.php'; ?>

<?php
// --- ฟังก์ชันแปลงวันที่ไทยเป็น yyyy-mm-dd ---
function thaiDateToIso($thaiDate) {
  // รับรูปแบบ dd/mm/yyyy หรือ d/m/yyyy (พ.ศ.)
  $parts = explode('/', $thaiDate);
  if (count($parts) === 3) {
    $d = (int)$parts[0];
    $m = (int)$parts[1];
    $y = (int)$parts[2] - 543; // แปลง พ.ศ. เป็น ค.ศ.
    return sprintf('%04d-%02d-%02d', $y, $m, $d);
  }
  return null;
}

// --- ฟังก์ชันแปลง yyyy-mm-dd เป็นวัน/เดือน/ปี พ.ศ. ---
function isoToThaiDate($isoDate) {
  if (!$isoDate || $isoDate === '0000-00-00') return '';
  $parts = explode('-', $isoDate);
  if (count($parts) === 3) {
    $y = (int)$parts[0] + 543;
    $m = (int)$parts[1];
    $d = (int)$parts[2];
    return sprintf('%02d/%02d/%04d', $d, $m, $y);
  }
  return '';
}
?>

<?php
// เพิ่ม SweetAlert ใน GET
?>
<?php if (!empty($uploadSuccess)): ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'สำเร็จ!',
        text: 'อัปโหลดเอกสารสำเร็จแล้ว',
        confirmButtonText: 'ตกลง'
      }).then(function() {
        window.location.href = 'document_upload.php';
      });
    });
  </script>
<?php endif; ?>