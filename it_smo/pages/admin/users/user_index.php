<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "จัดการข้อมูลผู้ใช้งาน | IT SMO";
$pageGroup = 'users';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';
require_once '../../../api/controllers/UserController.php';
require_once '../../../api/models/MajorModal.php';

// เรียกใช้ Auth ผ่าน getInstance
$auth = Auth::getInstance();
$user = $auth->getCurrentUser();

$allowedRoles = ['ผู้ดูแลระบบ','อาจารย์ที่ปรึกษา'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
  header('Location: /it_smo/pages/error/403.php');
  exit();
}

// Initialize database connection and controller
$database = new Database();
$db = $database->getConnection();
$userController = new UserController($db);
$majorModel = new MajorModal($db);
$majors = $majorModel->getAllMajors();
$subMajors = $majorModel->getAllSubMajors();

// Get current page and filters
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'created_at';
$order = isset($_GET['order']) ? trim($_GET['order']) : 'desc';
$major_id = isset($_GET['major_id']) ? trim($_GET['major_id']) : '';
$sub_major_id = isset($_GET['sub_major_id']) ? trim($_GET['sub_major_id']) : '';

// Prepare filter parameters
$filterParams = [
  'page' => $currentPage,
  'search' => $search,
  'role' => $role,
  'status' => $status,
  'sort' => $sort,
  'order' => $order,
  'major_id' => $major_id,
  'sub_major_id' => $sub_major_id
];

// Function to generate sort URL
function getSortUrl($column)
{
  global $sort, $order, $search, $role, $status;
  $newOrder = ($sort === $column && $order === 'asc') ? 'desc' : 'asc';
  $params = [
    'sort' => $column,
    'order' => $newOrder,
    'search' => $search,
    'role' => $role,
    'status' => $status
  ];
  return '?' . http_build_query($params);
}

// Function to get sort icon
function getSortIcon($column)
{
  global $sort, $order;
  if ($sort !== $column) {
    return '<i class="fas fa-sort"></i>';
  }
  return $order === 'asc' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
}

// Get users with pagination and filters
$result = $userController->handleRequest('GET', $filterParams);
$users = (is_array($result) && isset($result['data']) && is_array($result['data'])) ? $result['data'] : [];
$totalPages = (is_array($result) && isset($result['total_pages'])) ? $result['total_pages'] : 1;

// Get roles for filter
$rolesResult = $userController->handleRequest('GET', ['type' => 'roles']);
$roles = (is_array($rolesResult) && isset($rolesResult['data']) && is_array($rolesResult['data'])) ? $rolesResult['data'] : [];
?>

<?php if (isset($_GET['delete_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    ลบข้อมูลสมาชิกเรียบร้อยแล้ว
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (isset($_GET['edit_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    แก้ไขข้อมูลสมาชิกเรียบร้อยแล้ว
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (isset($_GET['add_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    เพิ่มข้อมูลสมาชิกเรียบร้อยแล้ว
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (isset($_GET['delete_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_GET['delete_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="container-fluid">
  <div class="page-header mb-4">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h1 class="page-title mb-1">จัดการข้อมูลสมาชิก</h1>
        <p class="text-muted mb-0">จัดการข้อมูลสมาชิกภายในสโมสรนักศึกษา</p>
      </div>
      <div class="d-flex gap-2">
      <a href="user_export.php" class="btn btn-success">
          <i class="fas fa-user-plus me-1"></i> ดาวน์โหลดข้อมูลสมาชิก
        </a>
        <a href="user_add.php" class="btn btn-success">
          <i class="fas fa-user-plus me-1"></i> เพิ่มผู้ใช้ทีละคน
        </a>
        <a href="user_import.php" class="btn btn-primary">
          <i class="fas fa-file-import me-1"></i> เพิ่มผู้ใช้หลายคน
        </a>
      </div>
    </div>
  </div>

      <!-- Search and Filter -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <form method="GET" class="row g-3">
            <div class="col-md-4">
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="search" placeholder="ค้นหาชื่อ, รหัส, อีเมล, เบอร์, สาขา ฯลฯ"
                  value="<?= htmlspecialchars($search) ?>">
              </div>
            </div>
            <div class="col-md-3">
              <select class="form-select" name="major_id" id="major_id">
                <option value="">ทุกสาขาวิชา</option>
                <?php foreach ($majors as $major): ?>
                <option value="<?= $major['major_id'] ?>" <?= $major_id == $major['major_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($major['major_name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <select class="form-select" name="sub_major_id" id="sub_major_id">
                <option value="">ทุกแขนงวิชา</option>
                <?php foreach ($subMajors as $sub): ?>
                <option value="<?= $sub['sub_major_id'] ?>" <?= $sub_major_id == $sub['sub_major_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($sub['sub_major_name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                  <i class="fas fa-filter me-1"></i> ค้นหา
                </button>
                <?php if (!empty($search) || !empty($major_id) || !empty($sub_major_id) || !empty($status)): ?>
                <a href="user_index.php" class="btn btn-secondary">
                  <i class="fas fa-times"></i>
                </a>
                <?php endif; ?>
              </div>
            </div>
          </form>
        </div>
      </div>

<div class="admin-users"></div>
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light text-center">
                <tr>
                  <th>
                    <a href="<?= getSortUrl('student_id') ?>" class="text-dark text-decoration-none">
                      ชื่อบัญชีผู้ใช้งาน<?= getSortIcon('student_id') ?>
                    </a>
                  </th>
                  <th>
                    <a href="<?= getSortUrl('first_name') ?>" class="text-dark text-decoration-none">
                      ชื่อ-นามสกุล <?= getSortIcon('first_name') ?>
                    </a>
                  </th>
                  <th>
                    <a href="<?= getSortUrl('email') ?>" class="text-dark text-decoration-none">
                      อีเมล <?= getSortIcon('email') ?>
                    </a>
                  </th>
                  <th>
                    <a href="<?= getSortUrl('role_name') ?>" class="text-dark text-decoration-none">
                      บทบาท <?= getSortIcon('role_name') ?>
                    </a>
                  </th>
                  <th>
                    <a href="<?= getSortUrl('status') ?>" class="text-dark text-decoration-none">
                      สถานะ <?= getSortIcon('status') ?>
                    </a>
                  </th>
                  <th>
                    <a href="<?= getSortUrl('created_at') ?>" class="text-dark text-decoration-none">
                      วันที่สมัคร <?= getSortIcon('created_at') ?>
                    </a>
                  </th>
                  <th class="text-center">การดำเนินการ</th>
                </tr>
              </thead>
              <tbody class="text-center">
                <?php if (empty($users)): ?>
                <tr>
                  <td colspan="7" class="text-center py-4">
                    <div class="text-muted">
                      <i class="fas fa-search fa-2x mb-2"></i>
                      <p class="mb-0">ไม่พบข้อมูลผู้ใช้</p>
                    </div>
                  </td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $row): // Changed $user to $row for clarity ?>
                <tr>
                  <td><?= htmlspecialchars($row['student_id']) ?></td>
                  <td>
                    <div class="d-flex align-items-center justify-content-start">
                      <img
                        src="<?= !empty($row['profile_image']) ? htmlspecialchars($row['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($row['first_name'] . ' ' . $row['last_name']) ?>"
                        class="rounded-circle me-2" width="32" height="32" alt="Profile">
                      <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                    </div>
                  </td>
                  <td class="text-start"><?= htmlspecialchars($row['email']) ?></td>
                  <td>
                    <span class="badge bg-<?= getRoleBadgeColor($row['role_name']) ?>">
                      <?= htmlspecialchars($row['role_name']) ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge 
                      <?php
                      if ($row['status'] === 'เปิดใช้งาน') echo 'bg-success';
                      elseif ($row['status'] === 'ปิดใช้งาน') echo 'bg-secondary';
                      else echo 'bg-danger';
                      ?>">
                      <?= htmlspecialchars($row['status']) ?>
                    </span>
                  </td>
                  <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                  <td class="text-center">
                    <?php
                    // Only show controls if the logged-in user is an admin,
                    // OR if the user being managed is NOT an admin.
                    $isTargetAdmin = $row['role_name'] === 'ผู้ดูแลระบบ';
                    $isCurrentUserAdmin = $_SESSION['user_role'] === 'ผู้ดูแลระบบ';

                    if ($isCurrentUserAdmin || !$isTargetAdmin):
                    ?>
                      <a href="user_edit.php?id=<?= $row['user_id'] ?>" class="btn btn-warning btn-sm" title="แก้ไข">
                        <i class="fas fa-edit"></i>
                      </a>
                      <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                        data-bs-target="#deleteUserModal" data-user-id="<?= $row['user_id'] ?>"
                        data-user-name="<?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>" title="ลบ">
                        <i class="fas fa-trash"></i>
                      </button>
                    <?php else: ?>
                      <span class="text-muted"><i class="fas fa-lock"></i></span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
          <nav class="mt-4">
            <ul class="pagination justify-content-center">
              <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link"
                  href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>">
                  <i class="fas fa-chevron-left"></i>
                </a>
              </li>

              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                <a class="page-link"
                  href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>">
                  <?= $i ?>
                </a>
              </li>
              <?php endfor; ?>

              <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link"
                  href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>">
                  <i class="fas fa-chevron-right"></i>
                </a>
              </li>
            </ul>
          </nav>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="user_delete.php">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteUserModalLabel"><i class="fas fa-trash me-2"></i>ยืนยันการลบผู้ใช้</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="user_id" id="deleteUserId">
          <p>คุณแน่ใจหรือไม่ว่าต้องการลบ <span id="deleteUserName" class="fw-bold text-danger"></span> ?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-danger">ลบ</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var deleteUserModal = document.getElementById('deleteUserModal');
  if (deleteUserModal) {
    deleteUserModal.addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget;
      var userId = button.getAttribute('data-user-id');
      var userName = button.getAttribute('data-user-name');
      document.getElementById('deleteUserId').value = userId;
      document.getElementById('deleteUserName').textContent = userName;
    });
  }
});
</script>

<?php
// Helper functions
function getRoleBadgeColor($role)
{
  $colors = [
    'admin' => 'danger',
    'staff' => 'primary',
    'advisor' => 'info',
    'president' => 'success',
    'vice_president' => 'warning',
    'secretary' => 'secondary',
    'committee' => 'dark',
    'member' => 'light'
  ];
  return $colors[$role] ?? 'secondary';
}

function getRoleName($role)
{
  $names = [
    'admin' => 'ผู้ดูแลระบบ',
    'staff' => 'เจ้าหน้าที่',
    'advisor' => 'อาจารย์ที่ปรึกษา',
    'president' => 'ประธานสโมสร',
    'vice_president' => 'รองประธาน',
    'secretary' => 'เลขานุการ',
    'committee' => 'กรรมการ',
    'member' => 'สมาชิก'
  ];
  return $names[$role] ?? 'ไม่ระบุ';
}

include '../../../includes/admin_footer.php';
?>