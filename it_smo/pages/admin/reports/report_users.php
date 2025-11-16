<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "รายงานผู้ใช้งาน | IT SMO";
$pageGroup = 'reports';
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// รับค่าฟิลเตอร์
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$major = isset($_GET['major']) ? trim($_GET['major']) : '';

// Get filter options
$roleStmt = $db->query("SELECT role_id, role_name FROM roles ORDER BY role_name");
$roleOptions = $roleStmt->fetchAll(PDO::FETCH_ASSOC);
$majorStmt = $db->query("SELECT major_id, major_name FROM majors ORDER BY major_name");
$majorOptions = $majorStmt->fetchAll(PDO::FETCH_ASSOC);

// Build WHERE clause
$where = [];
$params = [];
if ($search !== '') {
  $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $params[] = "%$search%";
}
if ($status !== '') {
  $where[] = "u.status = ?";
  $params[] = $status;
}
if ($role !== '') {
  $where[] = "u.role_id = ?";
  $params[] = $role;
}
if ($major !== '') {
  $where[] = "u.major_id = ?";
  $params[] = $major;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Latest 10 users ตาม filter
$sql = "SELECT u.*, r.role_name, m.major_name FROM users u LEFT JOIN roles r ON u.role_id = r.role_id LEFT JOIN majors m ON u.major_id = m.major_id $whereSql ORDER BY u.created_at DESC LIMIT 10";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$latestUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Summary by role
$roleStmt2 = $db->query("SELECT r.role_name, COUNT(u.user_id) AS total FROM roles r LEFT JOIN users u ON u.role_id = r.role_id GROUP BY r.role_id ORDER BY r.role_name");
$roles = $roleStmt2->fetchAll(PDO::FETCH_ASSOC);
// Summary by major
$majorStmt2 = $db->query("SELECT m.major_name, COUNT(u.user_id) AS total FROM majors m LEFT JOIN users u ON u.major_id = m.major_id GROUP BY m.major_id ORDER BY m.major_name");
$majors = $majorStmt2->fetchAll(PDO::FETCH_ASSOC);
// Summary by status
$statusStmt = $db->query("SELECT status, COUNT(*) AS total FROM users GROUP BY status");
$statuses = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="report_index.php" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ
    </a>
    <h2 class="mb-0">รายงานผู้ใช้งาน</h2>
    <span></span>
  </div>
  <form method="get" class="row g-2 mb-3">
    <div class="col-md-3">
      <input type="text" class="form-control" name="search" placeholder="ค้นหาชื่อ/อีเมล" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2">
      <select class="form-select" name="role">
        <option value="">ทุกบทบาท</option>
        <?php foreach ($roleOptions as $r): ?>
        <option value="<?= $r['role_id'] ?>" <?= $role == $r['role_id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['role_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select" name="major">
        <option value="">ทุกสาขาวิชา</option>
        <?php foreach ($majorOptions as $m): ?>
        <option value="<?= $m['major_id'] ?>" <?= $major == $m['major_id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['major_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" name="status">
        <option value="">ทุกสถานะ</option>
        <option value="เปิดใช้งาน" <?= $status === 'เปิดใช้งาน' ? 'selected' : '' ?>>เปิดใช้งาน</option>
        <option value="ปิดใช้งาน" <?= $status === 'ปิดใช้งาน' ? 'selected' : '' ?>>ปิดใช้งาน</option>
        <option value="รออนุมัติ" <?= $status === 'รออนุมัติ' ? 'selected' : '' ?>>รออนุมัติ</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> กรองข้อมูล</button>
    </div>
  </form>
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">สรุปตามบทบาท</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($roles as $roleSum): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($roleSum['role_name']) ?>
              <span class="badge bg-primary rounded-pill"><?= $roleSum['total'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">สรุปตามสาขาวิชา</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($majors as $majorSum): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($majorSum['major_name']) ?>
              <span class="badge bg-info rounded-pill"><?= $majorSum['total'] ?></span>
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
              <span class="badge bg-success rounded-pill"><?= $total ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>ผู้ใช้ล่าสุด 10 คน</span>
      <div>
        <a href="report_export.php?type=users&format=excel&search=<?=urlencode($search)?>&status=<?=urlencode($status)?>&role=<?=urlencode($role)?>&major=<?=urlencode($major)?>" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Export Excel</a>
        <a href="report_export.php?type=users&format=csv&search=<?=urlencode($search)?>&status=<?=urlencode($status)?>&role=<?=urlencode($role)?>&major=<?=urlencode($major)?>" class="btn btn-primary btn-sm"><i class="fas fa-file-csv"></i> Export CSV</a>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-light text-center">
          <tr>
            <th>รหัสนักศึกษา</th>
            <th>ชื่อ-นามสกุล</th>
            <th style="max-width:180px;">อีเมล</th>
            <th>บทบาท</th>
            <th>สาขาวิชา</th>
            <th>สถานะ</th>
            <th>วันที่สร้าง</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($latestUsers as $user): ?>
            <tr>
              <td><?= htmlspecialchars($user['student_id']) ?></td>
              <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
              <td class="text-truncate" style="max-width:180px;" title="<?= htmlspecialchars($user['email']) ?>">
                <?= htmlspecialchars($user['email']) ?>
              </td>
              <td><span class="badge bg-info"><?= htmlspecialchars($user['role_name']) ?></span></td>
              <td><?= htmlspecialchars($user['major_name']) ?></td>
              <td class="text-center">
                <?php
                  $statusColor = [
                    'เปิดใช้งาน' => 'success',
                    'ปิดใช้งาน' => 'secondary',
                    'รออนุมัติ' => 'warning'
                  ];
                  $color = $statusColor[$user['status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $color ?>"><?= htmlspecialchars($user['status']) ?></span>
              </td>
              <td><?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include '../../../includes/admin_footer.php'; ?>