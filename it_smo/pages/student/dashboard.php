<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../../api/config/Database.php';
require_once '../../api/controllers/UserController.php';
require_once '../../api/models/RoleModal.php';

// Initialize Database Connection
$database = new Database();
$db = $database->getConnection();

// Initialize Controllers and Models
$userController = new UserController($db);
$roleModel = new RoleModal($db);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: /it_smo/pages/public/login.php');
  exit();
}

// Check if user has student role
$allowedRoles = ['นักศึกษา'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
  header('Location: /it_smo/pages/public/login.php');
  exit();
}

// Set page info
$pageTitle = 'หน้าหลักนักศึกษา | IT SMO';

// ฟังก์ชันแปลงวันที่เป็นรูปแบบไทย
function thai_date($datetime)
{
  if (!$datetime) return '-';
  $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
  $ts = strtotime($datetime);
  $day = date('j', $ts);
  $month = $months[(int)date('n', $ts)];
  $year = date('Y', $ts) + 543;
  return "$day $month $year";
}

// --- ดึงข้อมูลสถิติ ---
// จำนวนเอกสารที่เข้าถึงได้
$stmt = $db->prepare("
  SELECT COUNT(*) 
  FROM documents d
  JOIN document_permissions dp ON d.document_id = dp.document_id
  JOIN roles r ON dp.role_id = r.role_id
  WHERE d.status = 'เผยแพร่' 
    AND r.role_name = 'นักศึกษา'
    AND dp.can_view = 1
");
$stmt->execute();
$publicDocs = $stmt->fetchColumn() ?: 0;

// จำนวนเอกสารที่เคยเข้าถึง
$stmt = $db->prepare("
  SELECT COUNT(DISTINCT document_id) 
  FROM document_access_logs 
  WHERE user_id = :user_id
");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$docAccess = $stmt->fetchColumn() ?: 0;

// ดึงข้อมูลผู้ใช้ (และ last_login, ชื่อ, สาขา)
$stmt = $db->prepare("SELECT u.*, m.major_name FROM users u LEFT JOIN majors m ON u.major_id = m.major_id WHERE u.user_id = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
$lastLogin = $userRow['last_login'] ?? null;
$fullName = ($userRow['first_name'] ?? '') . ' ' . ($userRow['last_name'] ?? '');
$studentId = $userRow['student_id'] ?? '';
$majorName = $userRow['major_name'] ?? '';

// --- ส่วนค้นหาเอกสาร ---
$searchTitle = isset($_GET['search_title']) ? trim($_GET['search_title']) : '';
$searchCategory = isset($_GET['search_category']) ? trim($_GET['search_category']) : '';
$searchUploader = isset($_GET['search_uploader']) ? trim($_GET['search_uploader']) : '';

try {
  // --- ดึงข้อมูลหมวดหมู่เอกสารทั้งหมด (สำหรับ dropdown) ---
  $stmt = $db->prepare("SELECT category_id, category_name FROM document_categories ORDER BY category_name");
  $stmt->execute();
  $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

  // --- ดึงข้อมูลเอกสารล่าสุดที่เข้าถึงได้ (พร้อม filter) ---
  $sql = "
    SELECT d.*, dc.category_name, u.first_name, u.last_name
    FROM documents d
    LEFT JOIN document_categories dc ON d.category_id = dc.category_id
    LEFT JOIN users u ON d.uploaded_by = u.user_id
    WHERE d.status = 'เผยแพร่'
      AND d.access_level = 'สาธารณะ'
  ";
  $params = [];
  if ($searchTitle !== '') {
    $sql .= " AND d.title LIKE :title ";
    $params['title'] = "%$searchTitle%";
  }
  if ($searchCategory !== '') {
    $sql .= " AND d.category_id = :cat ";
    $params['cat'] = $searchCategory;
  }
  if ($searchUploader !== '') {
    $sql .= " AND (u.first_name LIKE :uploader OR u.last_name LIKE :uploader) ";
    $params['uploader'] = "%$searchUploader%";
  }
  $sql .= " ORDER BY d.created_at DESC LIMIT 30 ";
  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $recentDocs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
  error_log($e->getMessage());
  $recentDocs = [];
  $allCategories = [];
}

include_once '../../includes/student_header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
      <a href="/it_smo/pages/student/dashboard.php">
        <i class="fas fa-home me-1"></i>หน้าหลัก
      </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">แผงควบคุม</li>
  </ol>
</nav>

<!-- Page Header -->
<div class="page-header mb-4">
  <div class="row align-items-center">
    <div class="col">
      <h1 class="page-title display-6 fw-bold text-gradient">หน้าหลักนักศึกษา</h1>
      <p class="text-muted mb-0">
        ยินดีต้อนรับ, <?= htmlspecialchars($fullName) ?>
        <small class="d-block">
          รหัสนักศึกษา: <?= htmlspecialchars($studentId) ?>
          <span class="ms-2">สาขา: <?= htmlspecialchars($majorName) ?></span>
        </small>
      </p>
    </div>
    <div class="col-auto">
      <div class="btn-group">
        <a href="documents.php" class="btn btn-primary btn-lg">
          <i class="fas fa-file me-2"></i>ดูเอกสารทั้งหมด
        </a>
        <button type="button" class="btn btn-primary btn-lg dropdown-toggle dropdown-toggle-split"
          data-bs-toggle="dropdown">
          <span class="visually-hidden">เมนูเพิ่มเติม</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>โปรไฟล์</a></li>
          <li><a class="dropdown-item" href="access_logs.php"><i class="fas fa-history me-2"></i>ประวัติการเข้าถึง</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Stats Cards -->
<div class="container-fluid py-4">
  <div class="row g-4 mb-4">
    <!-- Available Documents -->
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card card-stats h-100">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col">
              <h6 class="text-muted mb-2">เอกสารที่เข้าถึงได้</h6>
              <h3 class="mb-2"><?= $publicDocs ?></h3>
              <div class="d-flex align-items-center">
                <span class="badge bg-success-subtle text-success me-2">
                  <i class="fas fa-file me-1"></i>เอกสาร
                </span>
              </div>
            </div>
            <div class="col-auto">
              <div class="icon-shape bg-primary text-white">
                <i class="fas fa-file"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document Access -->
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card card-stats h-100">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col">
              <h6 class="text-muted mb-2">เอกสารที่เคยเข้าถึง</h6>
              <h3 class="mb-2"><?= $docAccess ?></h3>
              <div class="d-flex align-items-center">
                <span class="badge bg-info-subtle text-info">
                  <i class="fas fa-eye me-1"></i>เข้าถึงแล้ว
                </span>
              </div>
            </div>
            <div class="col-auto">
              <div class="icon-shape bg-info text-white">
                <i class="fas fa-eye"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Last Access -->
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card card-stats h-100">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col">
              <h6 class="text-muted mb-2">เข้าสู่ระบบล่าสุด</h6>
              <h3 class="mb-2"><?= isset($lastLogin) ? thai_date($lastLogin) : '-' ?></h3>
              <div class="d-flex align-items-center">
                <span class="badge bg-warning-subtle text-warning">
                  <i class="fas fa-clock me-1"></i>ครั้งล่าสุด
                </span>
              </div>
            </div>
            <div class="col-auto">
              <div class="icon-shape bg-warning text-white">
                <i class="fas fa-clock"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Content Row -->
  <div class="row">
    <div class="col-12">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
          <h5 class="card-title mb-0">เอกสารล่าสุด</h5>
          <a href="documents.php" class="btn btn-sm btn-neutral">ดูทั้งหมด</a>
        </div>
        <div class="card-body pt-3 pb-0">
          <!-- ฟอร์มค้นหาเอกสาร -->
          <form class="row g-2 mb-3" method="get" action="dashboard.php">
            <div class="col-md-4">
              <input type="text" class="form-control" name="search_title" placeholder="ค้นหาชื่อเอกสาร..." value="<?= htmlspecialchars($searchTitle) ?>">
            </div>
            <div class="col-md-3">
              <select class="form-select" name="search_category">
                <option value="">ทุกประเภท</option>
                <?php foreach ($allCategories as $cat): ?>
                  <option value="<?= $cat['category_id'] ?>" <?= $searchCategory == $cat['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <input type="text" class="form-control" name="search_uploader" placeholder="ค้นหาผู้อัปโหลด..." value="<?= htmlspecialchars($searchUploader) ?>">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> ค้นหา</button>
            </div>
          </form>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light text-center">
              <tr>
                <th scope="col">เอกสาร</th>
                <th scope="col">ประเภท</th>
                <th scope="col">ผู้อัปโหลด</th>
                <th scope="col">วันที่</th>
                <th scope="col">ขนาด</th>
                <th scope="col">การเข้าถึง</th>
                <th scope="col">ดูตัวอย่าง</th>
                <th scope="col">ดาวน์โหลด</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recentDocs)): foreach ($recentDocs as $doc): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="icon-shape icon-sm bg-info text-white me-2">
                        <i class="fas fa-file-<?= isset($doc['file_type']) && $doc['file_type'] == 'pdf' ? 'pdf' : 'alt' ?>"></i>
                      </div>
                      <span><?= htmlspecialchars($doc['title'] ?? 'ไม่ระบุชื่อ') ?></span>
                    </div>
                  </td>
                  <td class="text-center"><?= htmlspecialchars($doc['category_name'] ?? 'ไม่ระบุ') ?></td>
                  <td class="text-center">
                    <?= htmlspecialchars(($doc['first_name'] ?? '') . ' ' . ($doc['last_name'] ?? '')) ?>
                  </td>
                  <td class="text-center"><?= thai_date($doc['created_at'] ?? null) ?></td>
                  <td class="text-center">
                    <?= isset($doc['file_size']) ? number_format($doc['file_size'] / 1024, 2) . ' KB' : '-' ?>
                  </td>
                  <td class="text-center">
                    <span class="badge <?= isset($doc['access_level']) && $doc['access_level'] == 'สาธารณะ' ? 'bg-success' : 'bg-warning' ?>">
                      <?= htmlspecialchars($doc['access_level'] ?? 'ไม่ระบุ') ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <a href="<?= htmlspecialchars($doc['file_path']) ?>" class="btn btn-sm btn-info" target="_blank">
                      <i class="fas fa-eye"></i> ดูตัวอย่าง
                    </a>
                  </td>
                  <td class="text-center">
                    <a href="<?= htmlspecialchars($doc['file_path']) ?>" class="btn btn-sm btn-success" download>
                      <i class="fas fa-download"></i> ดาวน์โหลด
                    </a>
                  </td>
                </tr>
              <?php endforeach;
              else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted">ไม่มีเอกสารที่เข้าถึงได้</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once '../../includes/student_footer.php'; ?> 