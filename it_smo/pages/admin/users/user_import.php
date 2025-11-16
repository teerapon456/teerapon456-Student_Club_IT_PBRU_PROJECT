<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "นำเข้าข้อมูลผู้ใช้ | IT SMO";
$pageGroup = 'users';

require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';
require_once '../../../api/controllers/UserController.php';
require_once '../../../api/models/MajorModal.php';
require_once '../../../api/models/RoleModal.php';

// ลอง require autoload ถ้ามี
if (file_exists(dirname(__DIR__, 3) . '/vendor/autoload.php')) {
  require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
} else {
  // Manual autoload for PhpSpreadsheet (เฉพาะไฟล์ที่จำเป็น)
  $psBase = dirname(__DIR__, 3) . '/vendor/PhpSpreadsheet-4.3.1/src/PhpSpreadsheet/';

  // Core classes
  require_once $psBase . 'Exception.php';
  require_once $psBase . 'IComparable.php';
  require_once $psBase . 'Settings.php';
  require_once $psBase . 'Spreadsheet.php';

  // Document & Properties
  require_once $psBase . 'Document/Properties.php';
  require_once $psBase . 'Document/Security.php';

  // Worksheet
  require_once $psBase . 'Worksheet/Worksheet.php';
  require_once $psBase . 'Worksheet/Row.php';
  require_once $psBase . 'Worksheet/Column.php';

  // Cell
  require_once $psBase . 'Cell/Cell.php';
  require_once $psBase . 'Cell/DataType.php';
  require_once $psBase . 'Cell/IValueBinder.php';
  require_once $psBase . 'Cell/DefaultValueBinder.php';
  require_once $psBase . 'Cell/Coordinate.php';

  // Reader
  require_once $psBase . 'Reader/IReader.php';
  require_once $psBase . 'Reader/BaseReader.php';
  require_once $psBase . 'Reader/XlsBase.php';
  require_once $psBase . 'Reader/Xlsx.php';
  require_once $psBase . 'Reader/Xls.php';
  require_once $psBase . 'Reader/Xlsx/Namespaces.php';
  require_once $psBase . 'Reader/IReadFilter.php';
  require_once $psBase . 'Reader/DefaultReadFilter.php';

  // IOFactory
  require_once $psBase . 'IOFactory.php';
  // Shared
  require_once $psBase . 'Shared/File.php';

  // ReferenceHelper
  require_once $psBase . 'ReferenceHelper.php';
}

use PhpOffice\PhpSpreadsheet\IOFactory;

// เรียกใช้ Auth ผ่าน getInstance
$auth = Auth::getInstance();
$user = $auth->getCurrentUser();

$allowedRoles = ['ผู้ดูแลระบบ', 'ประธานสโมสร','เจ้าหน้าที่'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
  header('Location: /it_smo/pages/error/403.php');
  exit();
}

// Initialize database connection and controllers
$database = new Database();
$db = $database->getConnection();
$userController = new UserController($db);
$majorModel = new MajorModal($db);
$roleModel = new RoleModal($db);

// Get data from database
$majors = $majorModel->getAllMajors();
if (!is_array($majors)) $majors = [];
$subMajors = $majorModel->getAllSubMajors();
if (!is_array($subMajors)) $subMajors = [];
$roles = $roleModel->getAllRoles();
if (!is_array($roles)) $roles = [];

$successMessage = null;
$errorMessage = null;
$importedUsers = [];
$failedUsers = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_FILES['excel_file'])) {
    // Handle Excel import
    try {
      $inputFileName = $_FILES['excel_file']['tmp_name'];
      $spreadsheet = IOFactory::load($inputFileName);
      $worksheet = $spreadsheet->getActiveSheet();
      $rows = $worksheet->toArray();

      // Skip header row
      $header = array_map(function($h) {
        // ตัด comment ในวงเล็บออก เช่น role_name (ตำแหน่ง) => role_name
        return trim(preg_replace('/\s*\(.*\)$/u', '', $h));
      }, array_shift($rows));

      // Map ชื่อ column index
      $colIndex = array_flip($header);

      // เตรียมข้อมูล mapping name => id
      $roleMap = [];
      foreach ($roleModel->getAllRoles() as $r) {
        $roleMap[trim($r['role_name'])] = $r['role_id'];
      }
      $majorMap = [];
      foreach ($majorModel->getAllMajors() as $m) {
        $majorMap[trim($m['major_name'])] = $m['major_id'];
      }
      $subMajorMap = [];
      foreach ($majorModel->getAllSubMajors() as $sm) {
        $subMajorMap[trim($sm['sub_major_name'])] = $sm['sub_major_id'];
      }

      foreach ($rows as $rowIdx => $row) {
        if (empty($row[0])) continue; // Skip empty rows

        // เตรียมข้อมูลจาก column name
        $userData = [
          'student_id' => isset($colIndex['student_id']) ? trim($row[$colIndex['student_id']]) : '',
          'email' => isset($colIndex['email']) ? trim($row[$colIndex['email']]) : '',
          'first_name' => isset($colIndex['first_name']) ? trim($row[$colIndex['first_name']]) : '',
          'last_name' => isset($colIndex['last_name']) ? trim($row[$colIndex['last_name']]) : '',
          'phone' => isset($colIndex['phone']) ? trim($row[$colIndex['phone']]) : '',
          'status' => 'เปิดใช้งาน',
          'password' => password_hash(isset($colIndex['student_id']) ? trim($row[$colIndex['student_id']]) : '', PASSWORD_DEFAULT)
        ];
        // Map role_name => role_id
        if (isset($colIndex['role_name'])) {
          $roleName = trim($row[$colIndex['role_name']]);
          if (isset($roleMap[$roleName])) {
            $userData['role_id'] = $roleMap[$roleName];
          } else {
            $failedUsers[] = [
              'student_id' => $userData['student_id'],
              'error' => 'ไม่พบตำแหน่ง: ' . $roleName
            ]; continue;
          }
        }
        // Map major_name => major_id
        if (isset($colIndex['major_name'])) {
          $majorName = trim($row[$colIndex['major_name']]);
          if ($majorName === '' || !isset($majorMap[$majorName])) {
            $userData['major_id'] = null;
            if ($majorName !== '') {
              $failedUsers[] = [
                'student_id' => $userData['student_id'],
                'error' => 'ไม่พบสาขาวิชา: ' . $majorName
              ]; continue;
            }
          } else {
            $userData['major_id'] = $majorMap[$majorName];
          }
        }
        // Map sub_major_name => sub_major_id
        if (isset($colIndex['sub_major_name'])) {
          $subMajorName = trim((string)($row[$colIndex['sub_major_name']] ?? ''));
          if ($subMajorName === '' || !isset($subMajorMap[$subMajorName])) {
            $userData['sub_major_id'] = null;
            if ($subMajorName !== '') {
              $failedUsers[] = [
                'student_id' => $userData['student_id'],
                'error' => 'ไม่พบแขนงวิชา: ' . $subMajorName
              ]; continue;
            }
          } else {
            $userData['sub_major_id'] = $subMajorMap[$subMajorName];
          }
        }

        $result = $userController->handleRequest('POST', $userData);

        if ($result['success']) {
          $importedUsers[] = $userData['student_id'];
        } else {
          $failedUsers[] = [
            'student_id' => $userData['student_id'],
            'error' => $result['message'] ?? 'ไม่สามารถนำเข้าข้อมูลได้'
          ];
        }
      }

      if (!empty($importedUsers)) {
        $successMessage = 'นำเข้าข้อมูลสำเร็จ ' . count($importedUsers) . ' รายการ';
      }
    } catch (Exception $e) {
      $errorMessage = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
  } else {
    // Handle manual bulk add
    $users = $_POST['users'] ?? [];
    $successCount = 0;
    $errorCount = 0;
    $errors = [];

    foreach ($users as $index => $userData) {
      if (empty($userData['student_id']) || empty($userData['email'])) {
        continue; // Skip empty rows
      }

      // Prepare user data
      $data = [
        'student_id' => $userData['student_id'],
        'email' => $userData['email'],
        'password' => $userData['password'] ? password_hash($userData['password'], PASSWORD_DEFAULT) : password_hash($userData['student_id'], PASSWORD_DEFAULT),
        'first_name' => $userData['first_name'],
        'last_name' => $userData['last_name'],
        'phone' => $userData['phone'] ?? '',
        'role_id' => $userData['role_id'] ?? null,
        'major_id' => $userData['major_id'] ?? null,
        'sub_major_id' => $userData['sub_major_id'] ?? null,
        'status' => 'เปิดใช้งาน'
      ];

      // Handle profile image upload
      $profileImagePath = null;
      if (isset($_FILES['users']['name'][$index]['profile_image']) && $_FILES['users']['error'][$index]['profile_image'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 3) . '/uploads/profiles/';
        if (!file_exists($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['users']['name'][$index]['profile_image']);
        $uploadFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['users']['tmp_name'][$index]['profile_image'], $uploadFile)) {
          $profileImagePath = '/it_smo/uploads/profiles/' . $fileName;
        }
      }
      $data['profile_image'] = $profileImagePath;

      $result = $userController->handleRequest('POST', $data);

      if (is_array($result) && isset($result['success']) && $result['success']) {
        $successCount++;
      } else {
        $errorCount++;
        $errors[] = "แถวที่ " . ($index + 1) . ": " . (isset($result['message']) ? $result['message'] : "เกิดข้อผิดพลาด");
      }
    }

    if ($successCount > 0) {
      $successMessage = "เพิ่มผู้ใช้สำเร็จ {$successCount} คน";
    }
    if ($errorCount > 0) {
      $errorMessage = "ไม่สามารถเพิ่มผู้ใช้ได้ {$errorCount} คน<br>" . implode('<br>', $errors);
    }
  }
}
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users me-2"></i>เพิ่มผู้ใช้หลายคน</h2>
        <a href="./user_index.php" class="btn btn-secondary">
          <i class="fas fa-arrow-left me-1"></i> กลับ
        </a>
      </div>

      <?php if ($successMessage): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $successMessage ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <?php if ($errorMessage): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errorMessage ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <!-- Tabs -->
      <ul class="nav nav-tabs mb-4" id="addUserTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button"
            role="tab">
            <i class="fas fa-edit me-1"></i> เพิ่มข้อมูลด้วยตนเอง
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="excel-tab" data-bs-toggle="tab" data-bs-target="#excel" type="button" role="tab">
            <i class="fas fa-file-excel me-1"></i> นำเข้าจากไฟล์ Excel
          </button>
        </li>
      </ul>

      <!-- Tab Content -->
      <div class="tab-content" id="addUserTabsContent">
        <!-- Manual Input Tab -->
        <div class="tab-pane fade show active" id="manual" role="tabpanel">
          <div class="card">
            <div class="card-body">
              <form method="POST" id="bulkAddForm" class="needs-validation" novalidate>
                <div class="table-responsive">
                  <table class="table table-bordered" id="usersTable">
                    <thead>
                      <tr>
                        <th>รหัสนักศึกษา *</th>
                        <th>อีเมล *</th>
                        <th>รหัสผ่าน</th>
                        <th>ชื่อ *</th>
                        <th>นามสกุล *</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>บทบาท *</th>
                        <th>สาขาวิชา</th>
                        <th>แขนงวิชา</th>
                        <th>รูปภาพ</th>
                        <th>จัดการ</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><input type="text" class="form-control" name="users[0][student_id]" required
                            pattern="[0-9]{9}" maxlength="9"></td>
                        <td><input type="email" class="form-control" name="users[0][email]" required></td>
                        <td><input type="password" class="form-control" name="users[0][password]"
                            placeholder="เว้นว่างเพื่อใช้รหัสเริ่มต้น"></td>
                        <td><input type="text" class="form-control" name="users[0][first_name]" required></td>
                        <td><input type="text" class="form-control" name="users[0][last_name]" required></td>
                        <td><input type="tel" class="form-control" name="users[0][phone]" pattern="[0-9]{10}"></td>
                        <td>
                          <select class="form-select" name="users[0][role_id]" required>
                            <option value="">เลือกบทบาท</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['role_id'] ?>"><?= $role['role_name'] ?></option>
                            <?php endforeach; ?>
                          </select>
                        </td>
                        <td>
                          <select class="form-select major-select" name="users[0][major_id]" data-row="0" required>
                            <option value="">เลือกสาขาวิชา</option>
                            <?php foreach (
                              $majors as $major
                            ): ?>
                            <option value="<?= $major['major_id'] ?>"><?= htmlspecialchars($major['major_name']) ?>
                            </option>
                            <?php endforeach; ?>
                          </select>
                        </td>
                        <td>
                          <select class="form-select sub-major-select" name="users[0][sub_major_id]" data-row="0">
                            <option value="">เลือกแขนงวิชา</option>
                            <?php foreach ($subMajors as $sub): ?>
                            <option value="<?= $sub['sub_major_id'] ?>" data-major="<?= $sub['major_id'] ?>">
                              <?= htmlspecialchars($sub['sub_major_name']) ?>
                            </option>
                            <?php endforeach; ?>
                          </select>
                        </td>
                        <td><input type="file" class="form-control" name="users[0][profile_image]" accept="image/*">
                        </td>
                        <td>
                          <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-trash"></i>
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="mt-3">
                  <button type="button" class="btn btn-success" id="addRow">
                    <i class="fas fa-plus me-1"></i> เพิ่มแถว
                  </button>
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> บันทึก
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Excel Import Tab -->
        <div class="tab-pane fade" id="excel" role="tabpanel">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                      <label for="excel_file" class="form-label">เลือกไฟล์ Excel</label>
                      <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                      <div class="form-text">รองรับไฟล์ .xlsx และ .xls เท่านั้น</div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-upload me-1"></i> นำเข้าข้อมูล
                    </button>
                  </form>
                </div>
                <div class="col-md-6">
                  <div class="card bg-light">
                    <div class="card-body">
                      <h5 class="card-title">ดาวน์โหลดไฟล์ตัวอย่าง Excel</h5>
                      <a href="example.xlsx" class="btn btn-success mb-2" download>
                        <i class="fas fa-file-excel me-1"></i> ดาวน์โหลดไฟล์ Excel ตัวอย่าง
                      </a>
                      <p class="card-text mt-2">
                        กรุณาดาวน์โหลดไฟล์ Excel ตัวอย่างนี้ กรอกข้อมูลให้ครบถ้วน แล้วอัปโหลดกลับเข้าระบบ
                        และอย่าลืมลบข้อมูลตัวอย่างออกก่อน import เข้า<br>
                        <span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>ห้ามเปลี่ยนชื่อคอลัมน์ในไฟล์ตัวอย่าง</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php if (!empty($failedUsers)): ?>
      <div class="card shadow-sm mt-4">
        <div class="card-header">
          <h5 class="card-title mb-0">รายการที่นำเข้าไม่สำเร็จ</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>รหัสนักศึกษา</th>
                  <th>ข้อผิดพลาด</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($failedUsers as $user): ?>
                <tr>
                  <td><?= htmlspecialchars($user['student_id']) ?></td>
                  <td class="text-danger"><?= htmlspecialchars($user['error']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let rowCount = 1;

  // Form validation
  const form = document.getElementById('bulkAddForm');
  form.addEventListener('submit', function(event) {
    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  });

  // Add new row
  document.getElementById('addRow').addEventListener('click', function() {
    const tbody = document.querySelector('#usersTable tbody');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
      <td><input type="text" class="form-control" name="users[${rowCount}][student_id]" required pattern="[0-9]{9}" maxlength="9"></td>
      <td><input type="email" class="form-control" name="users[${rowCount}][email]" required></td>
      <td><input type="password" class="form-control" name="users[${rowCount}][password]" placeholder="เว้นว่างเพื่อใช้รหัสเริ่มต้น"></td>
      <td><input type="text" class="form-control" name="users[${rowCount}][first_name]" required></td>
      <td><input type="text" class="form-control" name="users[${rowCount}][last_name]" required></td>
      <td><input type="tel" class="form-control" name="users[${rowCount}][phone]" pattern="[0-9]{10}"></td>
      <td>
        <select class="form-select" name="users[${rowCount}][role_id]" required>
          <option value="">เลือกบทบาท</option>
          <?php foreach ($roles as $role): ?>
            <option value="<?= $role['role_id'] ?>"><?= $role['role_name'] ?></option>
          <?php endforeach; ?>
        </select>
      </td>
      <td>
        <select class="form-select major-select" name="users[${rowCount}][major_id]" data-row="${rowCount}" required>
          <option value="">เลือกสาขาวิชา</option>
          <?php foreach (
            $majors as $major
          ): ?>
            <option value="<?= $major['major_id'] ?>"><?= htmlspecialchars($major['major_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </td>
      <td>
        <select class="form-select sub-major-select" name="users[${rowCount}][sub_major_id]" data-row="${rowCount}">
          <option value="">เลือกแขนงวิชา</option>
          <?php foreach ($subMajors as $sub): ?>
            <option value="<?= $sub['sub_major_id'] ?>" data-major="<?= $sub['major_id'] ?>">
              <?= htmlspecialchars($sub['sub_major_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </td>
      <td><input type="file" class="form-control" name="users[${rowCount}][profile_image]" accept="image/*"></td>
      <td>
        <button type="button" class="btn btn-danger btn-sm remove-row">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    `;
    tbody.appendChild(newRow);
    rowCount++;
  });

  // Remove row
  document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-row')) {
      const tbody = document.querySelector('#usersTable tbody');
      if (tbody.children.length > 1) {
        e.target.closest('tr').remove();
      } else {
        alert('ต้องมีอย่างน้อย 1 แถว');
      }
    }
  });

  // Handle major selection change
  document.addEventListener('change', function(e) {
    if (e.target.classList.contains('major-select')) {
      const row = e.target.dataset.row;
      const majorId = e.target.value;
      const subMajorSelect = document.querySelector(`.sub-major-select[data-row="${row}"]`);

      // Filter sub-major options by major
      Array.from(subMajorSelect.options).forEach(opt => {
        if (!opt.value) return opt.style.display = '';
        opt.style.display = (opt.getAttribute('data-major') === majorId) ? '' : 'none';
      });
      // Reset sub_major_id if not match
      if (subMajorSelect.selectedOptions.length && subMajorSelect.selectedOptions[0].style.display === 'none') {
        subMajorSelect.value = '';
      }
    }
  });
});
</script>

<?php include_once '../../../includes/admin_footer.php'; ?>