<?php
$pageTitle = "จัดการสิทธิ์การใช้งาน | IT SMO";
$pageGroup = 'roles';
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';
require_once '../../../api/models/RoleModal.php';

$auth = Auth::getInstance();
$user = $auth->getCurrentUser();

// ตรวจสอบสิทธิ์ (เฉพาะผู้ดูแลระบบเท่านั้น)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ผู้ดูแลระบบ') {
  header('Location: /it_smo/pages/error/403.php');
  exit();
}

// Database connection
$database = new Database();
$db = $database->getConnection();
$roleModal = new RoleModal($db);

// Handle form submissions (CRUD)
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    switch ($_POST['action']) {
      case 'add':
        $data = [
          'role_name' => $_POST['role_name'] ?? '',
          'role_description' => $_POST['role_description'] ?? '',
          'created_at' => date('Y-m-d H:i:s')
        ];
        $roleModal->create($data);
        $message = 'เพิ่มบทบาทสำเร็จ';
        $messageType = 'success';
        break;
      case 'edit':
        $id = $_POST['id'] ?? null;
        if ($id) {
          $data = [
            'role_name' => $_POST['role_name'] ?? '',
            'role_description' => $_POST['role_description'] ?? ''
          ];
          $roleModal->update($id, $data);
          $message = 'แก้ไขบทบาทสำเร็จ';
          $messageType = 'success';
        }
        break;
      case 'delete':
        $id = $_POST['id'] ?? null;
        if ($id) {
          $roleModal->delete($id);
          $message = 'ลบบทบาทสำเร็จ';
          $messageType = 'success';
        }
        break;
    }
    // Redirect เพื่อป้องกันการ submit ซ้ำ
    header('Location: role_index.php');
    exit();
  }
}

// รับค่าการเรียงลำดับ
$allowedSort = ['role_id', 'role_name', 'created_at'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort) ? $_GET['sort'] : 'role_id';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'asc' : 'desc';

// Fetch all roles (เรียงลำดับ)
$roles = $roleModal->getAllRolesSorted($sort, $order);
?>
<div class="admin-roles">
  <div class="container-fluid py-4">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2><i class="fas fa-user-shield me-2"></i>จัดการสิทธิ์การใช้งาน</h2>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="fas fa-plus"></i> เพิ่มบทบาทใหม่
          </button>
        </div>
        <?php if ($message): ?>
          <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light text-center">
                  <tr>
                    <th><a href="?sort=role_id&order=<?= $sort === 'role_id' && $order === 'asc' ? 'desc' : 'asc' ?>" class="text-decoration-none text-dark">รหัส <?= $sort === 'role_id' ? ($order === 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?sort=role_name&order=<?= $sort === 'role_name' && $order === 'asc' ? 'desc' : 'asc' ?>" class="text-decoration-none text-dark">ชื่อบทบาท <?= $sort === 'role_name' ? ($order === 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th>คำอธิบาย</th>
                    <th><a href="?sort=created_at&order=<?= $sort === 'created_at' && $order === 'asc' ? 'desc' : 'asc' ?>" class="text-decoration-none text-dark">วันที่สร้าง <?= $sort === 'created_at' ? ($order === 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th>การดำเนินการ</th>
                  </tr>
                </thead>
                <tbody class="text-center">
                  <?php if (empty($roles)): ?>
                  <tr>
                    <td colspan="5" class="text-center py-4">
                      <div class="text-muted">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p class="mb-0">ไม่พบข้อมูลบทบาท</p>
                      </div>
                    </td>
                  </tr>
                  <?php else: ?>
                  <?php foreach ($roles as $role): ?>
                  <tr>
                    <td><?= htmlspecialchars($role['role_id']) ?></td>
                    <td><?= htmlspecialchars($role['role_name']) ?></td>
                    <td><?= htmlspecialchars($role['role_description']) ?></td>
                    <td><?= isset($role['created_at']) ? date('d/m/Y', strtotime($role['created_at'])) : '-' ?></td>
                    <td>
                      <button type="button" class="btn btn-sm btn-info" onclick='editRole(<?= json_encode($role) ?>)'>
                        <i class="fas fa-edit"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-danger" onclick='deleteRole(<?= htmlspecialchars($role['role_id']) ?>, "<?= htmlspecialchars($role['role_name']) ?>")'>
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
          <h5 class="modal-title">เพิ่มบทบาทใหม่</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">ชื่อบทบาท</label>
            <input type="text" class="form-control" name="role_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">คำอธิบาย</label>
            <textarea class="form-control" name="role_description" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary">บันทึก</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="editRoleId">
        <div class="modal-header">
          <h5 class="modal-title">แก้ไขบทบาท</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">ชื่อบทบาท</label>
            <input type="text" class="form-control" name="role_name" id="editRoleName" required>
          </div>
          <div class="mb-3">
            <label class="form-label">คำอธิบาย</label>
            <textarea class="form-control" name="role_description" id="editRoleDescription" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary">บันทึก</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Role Modal -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteRoleId">
        <div class="modal-header">
          <h5 class="modal-title">ยืนยันการลบ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>คุณต้องการลบบทบาท <strong id="deleteRoleName"></strong> ใช่หรือไม่?</p>
          <p class="text-danger">การลบจะไม่สามารถกู้คืนได้</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-danger">ลบ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Form validation
  (function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();

  // Edit role
  function editRole(role) {
  document.getElementById('editRoleId').value = role.id;
  document.getElementById('editRoleName').value = role.role_name || '';
  document.getElementById('editRoleDescription').value = role.role_description || '';
  new bootstrap.Modal(document.getElementById('editRoleModal')).show();
}

  // Delete role
  function deleteRole(id, name) {
    document.getElementById('deleteRoleId').value = id;
    document.getElementById('deleteRoleName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteRoleModal')).show();
  }
</script>

<?php include '../../../includes/admin_footer.php'; ?> 