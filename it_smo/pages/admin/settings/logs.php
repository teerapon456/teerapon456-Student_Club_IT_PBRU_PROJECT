<?php
$pageTitle = "ประวัติการใช้งาน | IT SMO";
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';

$auth = new Auth();
$user = $auth->getCurrentUser();



// ฟังก์ชันสำหรับดึงข้อมูลประวัติการใช้งาน
function getLogs($filters = [], $page = 1, $perPage = 20)
{
  // TODO: เชื่อมต่อกับฐานข้อมูลและดึงข้อมูลประวัติ
  return [
    'total' => 100,
    'logs' => [
      [
        'id' => 1,
        'user_id' => 1,
        'username' => 'admin',
        'action' => 'login',
        'description' => 'เข้าสู่ระบบ',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'created_at' => '2024-03-20 10:00:00'
      ],
      [
        'id' => 2,
        'user_id' => 1,
        'username' => 'admin',
        'action' => 'create_user',
        'description' => 'เพิ่มผู้ใช้ใหม่: john.doe',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'created_at' => '2024-03-20 10:05:00'
      ],
      [
        'id' => 3,
        'user_id' => 2,
        'username' => 'staff1',
        'action' => 'upload_document',
        'description' => 'อัปโหลดเอกสาร: รายงานประจำเดือน',
        'ip_address' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
        'created_at' => '2024-03-20 11:00:00'
      ]
    ]
  ];
}

// ฟังก์ชันสำหรับลบประวัติการใช้งาน
function deleteLogs($logIds)
{
  // TODO: เชื่อมต่อกับฐานข้อมูลและลบประวัติ
  return true;
}

// จัดการการส่งฟอร์ม
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['log_ids'])) {
    if (deleteLogs($_POST['log_ids'])) {
      $message = 'ลบประวัติการใช้งานสำเร็จ';
      $messageType = 'success';
    } else {
      $message = 'เกิดข้อผิดพลาดในการลบประวัติการใช้งาน';
      $messageType = 'danger';
    }
  }
}

// ดึงข้อมูลการกรอง
$filters = [
  'date_start' => $_GET['date_start'] ?? '',
  'date_end' => $_GET['date_end'] ?? '',
  'user' => $_GET['user'] ?? '',
  'action' => $_GET['action'] ?? '',
  'ip' => $_GET['ip'] ?? ''
];

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

$logsData = getLogs($filters, $page, $perPage);
$totalPages = ceil($logsData['total'] / $perPage);

// ฟังก์ชันสำหรับแปลงรหัสการกระทำเป็นข้อความ
function getActionName($action)
{
  $actions = [
    'login' => 'เข้าสู่ระบบ',
    'logout' => 'ออกจากระบบ',
    'create_user' => 'เพิ่มผู้ใช้',
    'edit_user' => 'แก้ไขผู้ใช้',
    'delete_user' => 'ลบผู้ใช้',
    'upload_document' => 'อัปโหลดเอกสาร',
    'delete_document' => 'ลบเอกสาร',
    'create_activity' => 'เพิ่มกิจกรรม',
    'edit_activity' => 'แก้ไขกิจกรรม',
    'delete_activity' => 'ลบกิจกรรม',
    'change_settings' => 'เปลี่ยนการตั้งค่า'
  ];
  return $actions[$action] ?? $action;
}
?>

<div class="admin-logs">
  <div class="container-fluid py-4">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-1">
      </div>

      <!-- Main Content -->
      <div class="col-md-9 col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>ประวัติการใช้งาน</h2>
          <div>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
              <i class="fas fa-trash"></i> ลบที่เลือก
            </button>
            <button type="button" class="btn btn-secondary" onclick="exportLogs()">
              <i class="fas fa-download"></i> ส่งออก
            </button>
          </div>
        </div>

        <?php if ($message): ?>
          <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!-- Filter Form -->
        <div class="card mb-4">
          <div class="card-body">
            <form method="GET" class="row g-3">
              <div class="col-md-3">
                <label class="form-label">วันที่เริ่มต้น</label>
                <input type="date" class="form-control" name="date_start"
                  value="<?= htmlspecialchars($filters['date_start']) ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">วันที่สิ้นสุด</label>
                <input type="date" class="form-control" name="date_end"
                  value="<?= htmlspecialchars($filters['date_end']) ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">ผู้ใช้</label>
                <input type="text" class="form-control" name="user"
                  value="<?= htmlspecialchars($filters['user']) ?>"
                  placeholder="ชื่อผู้ใช้">
              </div>
              <div class="col-md-2">
                <label class="form-label">การกระทำ</label>
                <select class="form-select" name="action">
                  <option value="">ทั้งหมด</option>
                  <option value="login" <?= $filters['action'] === 'login' ? 'selected' : '' ?>>เข้าสู่ระบบ</option>
                  <option value="logout" <?= $filters['action'] === 'logout' ? 'selected' : '' ?>>ออกจากระบบ</option>
                  <option value="create_user" <?= $filters['action'] === 'create_user' ? 'selected' : '' ?>>เพิ่มผู้ใช้</option>
                  <option value="edit_user" <?= $filters['action'] === 'edit_user' ? 'selected' : '' ?>>แก้ไขผู้ใช้</option>
                  <option value="delete_user" <?= $filters['action'] === 'delete_user' ? 'selected' : '' ?>>ลบผู้ใช้</option>
                  <option value="upload_document" <?= $filters['action'] === 'upload_document' ? 'selected' : '' ?>>อัปโหลดเอกสาร</option>
                  <option value="delete_document" <?= $filters['action'] === 'delete_document' ? 'selected' : '' ?>>ลบเอกสาร</option>
                  <option value="create_activity" <?= $filters['action'] === 'create_activity' ? 'selected' : '' ?>>เพิ่มกิจกรรม</option>
                  <option value="edit_activity" <?= $filters['action'] === 'edit_activity' ? 'selected' : '' ?>>แก้ไขกิจกรรม</option>
                  <option value="delete_activity" <?= $filters['action'] === 'delete_activity' ? 'selected' : '' ?>>ลบกิจกรรม</option>
                  <option value="change_settings" <?= $filters['action'] === 'change_settings' ? 'selected' : '' ?>>เปลี่ยนการตั้งค่า</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">IP Address</label>
                <input type="text" class="form-control" name="ip"
                  value="<?= htmlspecialchars($filters['ip']) ?>"
                  placeholder="IP Address">
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-search"></i> ค้นหา
                </button>
                <a href="?" class="btn btn-secondary">
                  <i class="fas fa-times"></i> ล้างตัวกรอง
                </a>
              </div>
            </form>
          </div>
        </div>

        <!-- Logs Table -->
        <div class="card">
          <div class="card-body">
            <form id="logsForm" method="POST">
              <input type="hidden" name="action" value="delete">
              <div class="container-fluid py-4">
                <div class="row">
                  <div class="col-12">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle">
                        <thead>
                          <tr>
                            <th width="40">
                              <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>วันที่</th>
                            <th>ผู้ใช้</th>
                            <th>การกระทำ</th>
                            <th>รายละเอียด</th>
                            <th>IP Address</th>
                            <th>Browser</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($logsData['logs'] as $log): ?>
                            <tr>
                              <td>
                                <input type="checkbox" class="form-check-input log-checkbox"
                                  name="log_ids[]" value="<?= $log['id'] ?>">
                              </td>
                              <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                              <td>
                                <a href="/it_smo/pages/admin/users/edit.php?id=<?= $log['user_id'] ?>">
                                  <?= htmlspecialchars($log['username']) ?>
                                </a>
                              </td>
                              <td><?= getActionName($log['action']) ?></td>
                              <td><?= htmlspecialchars($log['description']) ?></td>
                              <td><?= htmlspecialchars($log['ip_address']) ?></td>
                              <td>
                                <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                  title="<?= htmlspecialchars($log['user_agent']) ?>">
                                  <?= htmlspecialchars($log['user_agent']) ?>
                                </span>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </form>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
              <nav class="mt-4">
                <ul class="pagination justify-content-center">
                  <?php if ($page > 1): ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query($filters) ?>">
                        <i class="fas fa-chevron-left"></i>
                      </a>
                    </li>
                  <?php endif; ?>

                  <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                      <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($filters) ?>">
                        <?= $i ?>
                      </a>
                    </li>
                  <?php endfor; ?>

                  <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query($filters) ?>">
                        <i class="fas fa-chevron-right"></i>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </nav>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">ยืนยันการลบ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>คุณต้องการลบประวัติการใช้งานที่เลือกใช่หรือไม่?</p>
        <p class="text-danger">การลบจะไม่สามารถกู้คืนได้</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-danger" onclick="submitDelete()">ลบ</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Select all checkboxes
  document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.log-checkbox').forEach(function(checkbox) {
      checkbox.checked = this.checked;
    }, this);
  });

  // Confirm delete
  function confirmDelete() {
    var checkedBoxes = document.querySelectorAll('.log-checkbox:checked');
    if (checkedBoxes.length === 0) {
      alert('กรุณาเลือกประวัติการใช้งานที่ต้องการลบ');
      return;
    }
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
  }

  // Submit delete
  function submitDelete() {
    document.getElementById('logsForm').submit();
  }

  // Export logs
  function exportLogs() {
    var filters = <?= json_encode($filters) ?>;
    var queryString = Object.keys(filters)
      .filter(key => filters[key])
      .map(key => key + '=' + encodeURIComponent(filters[key]))
      .join('&');

    window.location.href = '/it_smo/api/logs/export.php?' + queryString;
  }
</script>

<?php include '../../../includes/admin_footer.php'; ?>