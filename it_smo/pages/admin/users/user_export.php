<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../vendor/autoload.php';
require_once '../../../includes/auth.php';
require_once '../../../api/config/Database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// เชื่อมต่อฐานข้อมูล
$database = new Database();
$db = $database->getConnection();

if (isset($_GET['format'])) {
  $format = $_GET['format'];

  // ดึง mapping id => name สำหรับ role, major, sub_major
  $roles = $db->query("SELECT role_id, role_name FROM roles")->fetchAll(PDO::FETCH_KEY_PAIR);
  $majors = $db->query("SELECT major_id, major_name FROM majors")->fetchAll(PDO::FETCH_KEY_PAIR);
  $subMajors = $db->query("SELECT sub_major_id, sub_major_name FROM sub_majors")->fetchAll(PDO::FETCH_KEY_PAIR);

  // ฟิลด์และ comment (หัวตาราง)
  $fields = [
    'student_id' => 'รหัสนักศึกษา',
    'email' => 'อีเมล',
    'first_name' => 'ชื่อ',
    'last_name' => 'นามสกุล',
    'phone' => 'เบอร์โทรศัพท์',
    'role_name' => 'ตำแหน่ง',
    'major_name' => 'สาขาวิชา',
    'sub_major_name' => 'แขนงวิชา',
    'profile_image' => 'ที่อยู่รูปโปรไฟล์',
    'status' => 'สถานะผู้ใช้',
    'last_login' => 'วันที่เข้าสู่ระบบล่าสุด',
    'created_at' => 'วันที่สร้างบัญชี',
    'updated_at' => 'วันที่แก้ไขล่าสุด'
  ];

  // ดึงข้อมูลผู้ใช้ (JOIN เพื่อดึงชื่อ role, major, sub_major)
  $query = "SELECT u.student_id, u.email, u.first_name, u.last_name, u.phone, r.role_name, m.major_name, sm.sub_major_name, u.profile_image, u.status, u.last_login, u.created_at, u.updated_at
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN majors m ON u.major_id = m.major_id
            LEFT JOIN sub_majors sm ON u.sub_major_id = sm.sub_major_id
            ORDER BY u.created_at DESC";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if ($format === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // หัวตาราง: field (comment)
    $headerRow = [];
    foreach ($fields as $field => $comment) {
      $headerRow[] = $field . ' (' . $comment . ')';
    }
    $sheet->fromArray([$headerRow], NULL, 'A1');

    // จัดรูปแบบหัวข้อ
    $headerStyle = [
      'font' => ['bold' => true],
      'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'E2EFDA']
      ]
    ];
    $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

    // ใส่ข้อมูล
    $row = 2;
    foreach ($users as $user) {
      $col = 'A';
      foreach (array_keys($fields) as $field) {
        $sheet->setCellValue($col . $row, $user[$field]);
        $col++;
      }
      $row++;
    }
    foreach (range('A', 'M') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    $writer = new Xlsx($spreadsheet);
    $filename = 'users_export_' . date('Y-m-d_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
  } elseif ($format === 'sql') {
    // SQL: ยังใช้ id เหมือนเดิม
    $query = "SELECT user_id, student_id, email, first_name, last_name, phone, role_id, major_id, sub_major_id, profile_image, status, last_login, created_at, updated_at FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $filename = 'users_export_' . date('Y-m-d_His') . '.sql';
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "SET NAMES utf8mb4;\n\n";
    foreach ($users as $user) {
      $fields = [];
      $values = [];
      foreach ($user as $field => $value) {
        $fields[] = "`$field`";
        $values[] = $value === null ? "NULL" : "'" . addslashes($value) . "'";
      }
      echo "INSERT INTO `users` (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ");\n";
    }
    exit;
  }
}

// หน้าเว็บสำหรับเลือกรูปแบบการส่งออก
$pageTitle = "ส่งออกข้อมูลผู้ใช้ | IT SMO";
require_once '../../../includes/admin_header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
      <div class="card shadow-lg border-0">
        <div class="card-body p-5">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h2 class="mb-1"><i class="fas fa-file-export me-2 text-primary"></i>ส่งออกข้อมูลผู้ใช้</h2>
              <p class="text-muted mb-0">เลือกประเภทไฟล์ที่ต้องการดาวน์โหลดข้อมูลสมาชิกทั้งหมด</p>
            </div>
            <a href="./user_index.php" class="btn btn-outline-secondary">
              <i class="fas fa-arrow-left me-1"></i> กลับ
            </a>
          </div>

          <div class="row g-4 justify-content-center">
            <div class="col-md-6">
              <div class="card h-100 border-0 bg-success bg-gradient text-white text-center export-card">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                  <i class="fas fa-file-excel fa-3x mb-3"></i>
                  <h4 class="mb-2">Excel (.xlsx)</h4>
                  <p class="mb-4">ไฟล์ excel ข้อมูลสมาชิกทั้งหมดที่จัดเก็บในฐานข้อมูล</p>
                  <a href="?format=excel" class="btn btn-light btn-lg px-4 shadow-sm">
                    <i class="fas fa-download me-2"></i> ดาวน์โหลด Excel
                  </a>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<style>
.export-card {
  transition: transform 0.2s, box-shadow 0.2s;
}
.export-card:hover {
  transform: translateY(-6px) scale(1.03);
  box-shadow: 0 8px 32px rgba(44, 62, 80, 0.18);
  z-index: 2;
}
</style>

<?php include '../../../includes/admin_footer.php'; ?>