<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "รายงานเอกสาร | IT SMO";
$pageGroup = 'reports';
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// รับค่าฟิลเตอร์
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';

// Build WHERE clause
$where = [];
$params = [];
if ($search !== '') {
  $where[] = "(d.title LIKE ? OR d.description LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
}
if ($category !== '') {
  $where[] = "d.category_id = ?";
  $params[] = $category;
}
if ($status !== '') {
  $where[] = "d.status = ?";
  $params[] = $status;
}
if ($year !== '') {
  $where[] = "YEAR(d.created_at) = ?";
  $params[] = $year;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Query ข้อมูลเอกสาร (10 รายการล่าสุด)
$sql = "SELECT d.*, c.category_name, u.first_name, u.last_name, u.user_id as uploaded_by
        FROM documents d
        LEFT JOIN document_categories c ON d.category_id = c.category_id
        LEFT JOIN users u ON d.uploaded_by = u.user_id
        $whereSql
        ORDER BY d.created_at DESC
        LIMIT 10";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$latestDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query ข้อมูลเอกสารทั้งหมด (สำหรับสรุป)
$sqlAll = "SELECT d.*, c.category_name, u.first_name, u.last_name, u.user_id as uploaded_by
        FROM documents d
        LEFT JOIN document_categories c ON d.category_id = c.category_id
        LEFT JOIN users u ON d.uploaded_by = u.user_id
        $whereSql";
$stmtAll = $db->prepare($sqlAll);
$stmtAll->execute($params);
$allDocs = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

// สรุปตามหมวดหมู่
$categories = [];
foreach ($allDocs as $doc) {
  if (!isset($categories[$doc['category_id']])) {
    $categories[$doc['category_id']] = [
      'category_name' => $doc['category_name'],
      'total' => 0
    ];
  }
  $categories[$doc['category_id']]['total']++;
}

// สรุปตามสถานะ
$statuses = [];
foreach ($allDocs as $doc) {
  $statuses[$doc['status']] = ($statuses[$doc['status']] ?? 0) + 1;
}

// สรุปตามผู้อัปโหลด
$uploaders = [];
foreach ($allDocs as $doc) {
  $uploaderKey = $doc['uploaded_by'] ?? ($doc['first_name'] . $doc['last_name']);
  if (!isset($uploaders[$uploaderKey])) {
    $uploaders[$uploaderKey] = [
      'first_name' => $doc['first_name'],
      'last_name' => $doc['last_name'],
      'total' => 0
    ];
  }
  $uploaders[$uploaderKey]['total']++;
}

// Get categories for filter
$catStmt = $db->query("SELECT category_id, category_name FROM document_categories ORDER BY category_name");
$categoriesForFilter = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Get years for filter
$yearStmt = $db->query("SELECT DISTINCT YEAR(created_at) AS year FROM documents ORDER BY year DESC");
$yearsForFilter = $yearStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="report_index.php" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ
    </a>
    <h2 class="mb-0">รายงานเอกสาร</h2>
    <span></span>
  </div>

  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">สรุปตามหมวดหมู่</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($categories as $cat): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($cat['category_name']) ?>
              <span class="badge bg-primary rounded-pill"><?= $cat['total'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">สรุปตามสถานะ</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($statuses as $statusName => $total): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($statusName) ?>
              <span class="badge bg-info rounded-pill"><?= $total ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">สรุปตามผู้ส่ง</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($uploaders as $u): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
              <span class="badge bg-success rounded-pill"><?= $u['total'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>เอกสารล่าสุด 10 รายการ</span>
      <div>
        <a href="report_export.php?type=documents&format=excel&search=<?=urlencode($search)?>&year=<?=urlencode($year)?>&category=<?=urlencode($category)?>&status=<?=urlencode($status)?>" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Export Excel</a>
        <a href="report_export.php?type=documents&format=csv&search=<?=urlencode($search)?>&year=<?=urlencode($year)?>&category=<?=urlencode($category)?>&status=<?=urlencode($status)?>" class="btn btn-primary btn-sm"><i class="fas fa-file-csv"></i> Export CSV</a>
      </div>
    </div>
    <form method="get" class="row g-2 mb-3">
    <div class="col-md-3">
      <input type="text" class="form-control" name="search" placeholder="ค้นหาชื่อเอกสาร/คำอธิบาย" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2">
      <select class="form-select" name="year">
        <option value="">ทุกปี</option>
        <?php foreach ($yearsForFilter as $y): ?>
        <option value="<?= $y['year'] ?>" <?= $year == $y['year'] ? 'selected' : '' ?>><?= $y['year'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select" name="category">
        <option value="">ทุกหมวดหมู่</option>
        <?php foreach ($categoriesForFilter as $cat): ?>
        <option value="<?= $cat['category_id'] ?>" <?= $category == $cat['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" name="status">
        <option value="">ทุกสถานะ</option>
        <option value="ร่าง" <?= $status === 'ร่าง' ? 'selected' : '' ?>>ร่าง</option>
        <option value="เผยแพร่" <?= $status === 'เผยแพร่' ? 'selected' : '' ?>>เผยแพร่</option>
        <option value="ยกเลิก" <?= $status === 'ยกเลิก' ? 'selected' : '' ?>>ยกเลิก</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> กรองข้อมูล</button>
    </div>
  </form>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-light text-center">
          <tr>
            <th>ชื่อเอกสาร</th>
            <th>หมวดหมู่</th>
            <th style="max-width:200px;">คำอธิบาย</th>
            <th>สถานะ</th>
            <th>ผู้อัปโหลด</th>
            <th>วันที่อัปโหลด</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($latestDocs as $doc): ?>
            <tr>
              <td><?= htmlspecialchars($doc['title']) ?></td>
              <td><span class="badge bg-info"><?= htmlspecialchars($doc['category_name']) ?></span></td>
              <td class="text-truncate" style="max-width:200px;" title="<?= htmlspecialchars($doc['description']) ?>">
                <?= htmlspecialchars($doc['description']) ?>
              </td>
              <td class="text-center">
                <?php
                  $statusColor = [
                    'เผยแพร่' => 'success',
                    'ร่าง' => 'warning',
                    'ยกเลิก' => 'secondary'
                  ];
                  $color = $statusColor[$doc['status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $color ?>"><?= htmlspecialchars($doc['status']) ?></span>
              </td>
              <td><?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></td>
              <td><?= htmlspecialchars(date('d/m/Y', strtotime($doc['created_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include '../../../includes/admin_footer.php'; ?>