<?php
$pageTitle = "ตั้งค่าบทบาท | IT SMO";
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';

$auth = new Auth();
$user = $auth->getCurrentUser();



// ฟังก์ชันสำหรับดึงข้อมูลบทบาททั้งหมด
function getAllRoles()
{
  // TODO: เชื่อมต่อกับฐานข้อมูลและดึงข้อมูลบทบาท
  return [
    [
      'id' => 1,
      'name' => 'ผู้ดูแลระบบ',
      'code' => 'admin',
      'description' => 'ผู้ดูแลระบบทั้งหมด',
      'permissions' => ['all'],
      'is_system' => true,
      'user_count' => 1
    ],
    [
      'id' => 2,
      'name' => 'เจ้าหน้าที่',
      'code' => 'staff',
      'description' => 'เจ้าหน้าที่สโมสร',
      'permissions' => ['manage_documents', 'manage_activities', 'view_reports'],
      'is_system' => true,
      'user_count' => 2
    ],
    [
      'id' => 3,
      'name' => 'อาจารย์ที่ปรึกษา',
      'code' => 'advisor',
      'description' => 'อาจารย์ที่ปรึกษาสโมสร',
      'permissions' => ['view_documents', 'view_activities', 'view_reports'],
      'is_system' => true,
      'user_count' => 1
    ],
    [
      'id' => 4,
      'name' => 'ประธานสโมสร',
      'code' => 'president',
      'description' => 'ประธานสโมสรนักศึกษา',
      'permissions' => ['manage_activities', 'view_documents', 'view_reports'],
      'is_system' => true,
      'user_count' => 1
    ],
    [
      'id' => 5,
      'name' => 'รองประธาน',
      'code' => 'vice_president',
      'description' => 'รองประธานสโมสรนักศึกษา',
      'permissions' => ['manage_activities', 'view_documents', 'view_reports'],
      'is_system' => true,
      'user_count' => 1
    ],
    [
      'id' => 6,
      'name' => 'เลขานุการ',
      'code' => 'secretary',
      'description' => 'เลขานุการสโมสรนักศึกษา',
      'permissions' => ['manage_documents', 'view_activities', 'view_reports'],
      'is_system' => true,
      'user_count' => 1
    ],
    [
      'id' => 7,
      'name' => 'กรรมการ',
      'code' => 'committee',
      'description' => 'กรรมการสโมสรนักศึกษา',
      'permissions' => ['view_documents', 'view_activities'],
      'is_system' => true,
      'user_count' => 5
    ]
  ];
}

// ฟังก์ชันสำหรับดึงข้อมูลสิทธิ์ทั้งหมด
function getAllPermissions()
{
  return [
    'all' => 'สิทธิ์ทั้งหมด',
    'manage_users' => 'จัดการผู้ใช้',
    'manage_roles' => 'จัดการบทบาท',
    'manage_settings' => 'จัดการตั้งค่าระบบ',
    'manage_documents' => 'จัดการเอกสาร',
    'view_documents' => 'ดูเอกสาร',
    'manage_activities' => 'จัดการกิจกรรม',
    'view_activities' => 'ดูกิจกรรม',
    'view_reports' => 'ดูรายงาน',
    'manage_logs' => 'จัดการประวัติการใช้งาน'
  ];
}

// ฟังก์ชันสำหรับบันทึกบทบาท
function saveRole($roleData)
{
  // TODO: เชื่อมต่อกับฐานข้อมูลและบันทึกบทบาท
  return true;
}

// ฟังก์ชันสำหรับลบบทบาท
function deleteRole($roleId)
{
  // TODO: เชื่อมต่อกับฐานข้อมูลและลบบทบาท
  return true;
}

// จัดการการส่งฟอร์ม
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    switch ($_POST['action']) {
      case 'add':
      case 'edit':
        $roleData = [
          'name' => $_POST['name'] ?? '',
          'code' => $_POST['code'] ?? '',
          'description' => $_POST['description'] ?? '',
          'permissions' => $_POST['permissions'] ?? [],
          'is_system' => isset($_POST['is_system'])
        ];

        if (isset($_POST['id'])) {
          $roleData['id'] = $_POST['id'];
        }

        if (saveRole($roleData)) {
          $message = 'บันทึกบทบาทสำเร็จ';
          $messageType = 'success';
        } else {
          $message = 'เกิดข้อผิดพลาดในการบันทึกบทบาท';
          $messageType = 'danger';
        }
        break;

      case 'delete':
        if (isset($_POST['id']) && deleteRole($_POST['id'])) {
          $message = 'ลบบทบาทสำเร็จ';
          $messageType = 'success';
        } else {
          $message = 'เกิดข้อผิดพลาดในการลบบทบาท';
          $messageType = 'danger';
        }
        break;
    }
  }
}

$roles = getAllRoles();
$permissions = getAllPermissions();
?>

<div class="admin-roles">
  <div class="container-fluid py-4">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-1">
      </div>
      <!-- Main Content -->
      <div class="col-md-9 col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>ตั้งค่าบทบาท</h2>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="fas fa-plus"></i> เพิ่มบทบาทใหม่
          </button>
        </div>

        <?php if ($message): ?>
          <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!-- Roles Table -->
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>ชื่อบทบาท</th>
                    <th>รหัส</th>
                    <th>คำอธิบาย</th>
                    <th>จำนวนผู้ใช้</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($roles as $role): ?>
                    <tr>
                      <td><?= htmlspecialchars($role['name']) ?></td>
                      <td><code><?= htmlspecialchars($role['code']) ?></code></td>
                      <td><?= htmlspecialchars($role['description']) ?></td>
                      <td><?= number_format($role['user_count']) ?></td>
                      <td>
                        <?php if ($role['is_system']): ?>
                          <span class="badge bg-primary">ระบบ</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">กำหนดเอง</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <button type="button" class="btn btn-sm btn-info"
                          onclick="editRole(<?= htmlspecialchars(json_encode($role)) ?>)">
                          <i class="fas fa-edit"></i>
                        </button>
                        <?php if (!$role['is_system']): ?>
                          <button type="button" class="btn btn-sm btn-danger"
                            onclick="deleteRole(<?= $role['id'] ?>, '<?= htmlspecialchars($role['name']) ?>')">
                            <i class="fas fa-trash"></i>
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
          <h5 class="modal-title">เพิ่มบทบาทใหม่</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">ชื่อบทบาท</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">รหัส</label>
              <input type="text" class="form-control" name="code" required
                pattern="[a-z0-9_]+" title="ใช้ตัวอักษรพิมพ์เล็ก ตัวเลข และเครื่องหมายขีดล่างเท่านั้น">
              <small class="text-muted">ใช้ตัวอักษรพิมพ์เล็ก ตัวเลข และเครื่องหมายขีดล่างเท่านั้น</small>
            </div>
            <div class="col-12">
              <label class="form-label">คำอธิบาย</label>
              <textarea class="form-control" name="description" rows="2"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">สิทธิ์</label>
              <div class="row g-2">
                <?php foreach ($permissions as $code => $name): ?>
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox"
                        name="permissions[]" value="<?= $code ?>">
                      <label class="form-check-label">
                        <?= htmlspecialchars($name) ?>
                      </label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_system">
                <label class="form-check-label">เป็นบทบาทของระบบ</label>
              </div>
              <small class="text-muted">บทบาทของระบบไม่สามารถลบได้</small>
            </div>
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="editRoleId">
        <div class="modal-header">
          <h5 class="modal-title">แก้ไขบทบาท</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">ชื่อบทบาท</label>
              <input type="text" class="form-control" name="name" id="editRoleName" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">รหัส</label>
              <input type="text" class="form-control" name="code" id="editRoleCode" required
                pattern="[a-z0-9_]+" title="ใช้ตัวอักษรพิมพ์เล็ก ตัวเลข และเครื่องหมายขีดล่างเท่านั้น" readonly>
              <small class="text-muted">ไม่สามารถแก้ไขรหัสได้</small>
            </div>
            <div class="col-12">
              <label class="form-label">คำอธิบาย</label>
              <textarea class="form-control" name="description" id="editRoleDescription" rows="2"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">สิทธิ์</label>
              <div class="row g-2" id="editRolePermissions">
                <?php foreach ($permissions as $code => $name): ?>
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox"
                        name="permissions[]" value="<?= $code ?>">
                      <label class="form-check-label">
                        <?= htmlspecialchars($name) ?>
                      </label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_system" id="editRoleIsSystem">
                <label class="form-check-label">เป็นบทบาทของระบบ</label>
              </div>
              <small class="text-muted">บทบาทของระบบไม่สามารถลบได้</small>
            </div>
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
          <p class="text-danger">การลบจะไม่สามารถกู้คืนได้ และผู้ใช้ที่มีบทบาทนี้จะถูกเปลี่ยนเป็นผู้ใช้ทั่วไป</p>
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
    document.getElementById('editRoleName').value = role.name;
    document.getElementById('editRoleCode').value = role.code;
    document.getElementById('editRoleDescription').value = role.description;
    document.getElementById('editRoleIsSystem').checked = role.is_system;

    // Reset permissions
    var checkboxes = document.querySelectorAll('#editRolePermissions input[type="checkbox"]');
    checkboxes.forEach(function(checkbox) {
      checkbox.checked = role.permissions.includes(checkbox.value);
    });

    // Show modal
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