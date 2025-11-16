<?php
session_start();
$pageTitle = 'หน้าหลัก | IT SMO';
$pageName = 'dashboard';
$pageGroup = 'dashboard';
require_once '../../../api/config/Database.php';
$database = new Database();
$pdo = $database->getConnection();
include_once '../../../includes/admin_header.php';

function thai_date($datetime) {
  $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
  $ts = strtotime($datetime);
  $day = date('j', $ts);
  $month = $months[(int)date('n', $ts)];
  $year = date('Y', $ts) + 543;
  return "$day $month $year";
}

$user_id = $_SESSION['user_id'];
// สถิติเอกสารทั้งหมด
$totalDocs = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
$publishedDocs = $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'เผยแพร่'")->fetchColumn();
$draftDocs = $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'ร่าง'")->fetchColumn();

// เอกสารล่าสุด (ทุกสถานะ)
$recentDocs = $pdo->query("
  SELECT d.document_id, d.title, d.file_path, c.category_name, d.created_at, d.status
  FROM documents d
  LEFT JOIN document_categories c ON d.category_id = c.category_id
  ORDER BY d.created_at DESC
  LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
.dashboard-card {
  border: none;
  border-radius: 1rem;
  box-shadow: 0 2px 16px rgba(0,0,0,0.07);
  transition: box-shadow .2s;
}
.dashboard-card:hover {
  box-shadow: 0 4px 32px rgba(0,0,0,0.13);
}
.stat-icon {
  width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;
  border-radius: 50%; font-size: 2rem; margin-bottom: 1rem;
}
.stat-icon.bg1 { background: linear-gradient(135deg,#4e73df,#224abe); color: #fff; }
.stat-icon.bg2 { background: linear-gradient(135deg,#1cc88a,#13855c); color: #fff; }
.stat-icon.bg3 { background: linear-gradient(135deg,#f6c23e,#dda20a); color: #fff; }
.table thead th { background: #f8f9fc; }
.table tbody tr:hover {
  background-color: rgba(78, 115, 223, 0.05);
  transform: translateY(-1px);
  transition: all 0.2s ease;
}
.btn-group .btn {
  transition: all 0.2s ease;
}
.btn-group .btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.document-title {
  cursor: pointer;
  transition: color 0.2s ease;
}
.document-title:hover {
  color: #4e73df !important;
}
</style>
<div class="container py-4">
  <h2 class="fw-bold mb-4"><i class="bi bi-folder2-open me-2 text-primary"></i>หน้าหลักสมาชิกสโมสรนักศึกษา</h2>
  <div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
      <div class="card dashboard-card text-center">
        <div class="card-body">
          <div class="stat-icon bg1 mb-2"><i class="bi bi-file-earmark-text"></i></div>
          <h2 class="fw-bold mb-0"><?= $totalDocs ?></h2>
          <div class="text-muted">เอกสารทั้งหมด</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card dashboard-card text-center">
        <div class="card-body">
          <div class="stat-icon bg2 mb-2"><i class="bi bi-cloud-arrow-up"></i></div>
          <h2 class="fw-bold mb-0"><?= $publishedDocs ?></h2>
          <div class="text-muted">เผยแพร่แล้ว</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card dashboard-card text-center">
        <div class="card-body">
          <div class="stat-icon bg3 mb-2"><i class="bi bi-pencil-square"></i></div>
          <h2 class="fw-bold mb-0"><?= $draftDocs ?></h2>
          <div class="text-muted">ร่าง</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3 d-flex align-items-center">
      <a href="../documents/document_upload.php" class="btn btn-primary w-100">
        <i class="bi bi-cloud-arrow-up"></i> อัปโหลดเอกสารใหม่
      </a>
    </div>
  </div>
  <div class="row g-4 mb-4">
    <div class="col-lg-8">
      <div class="card dashboard-card h-100">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0">เอกสารล่าสุด</h5>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>ชื่อเอกสาร</th>
                <th>หมวดหมู่</th>
                <th>วันที่</th>
                <th>สถานะ</th>
                <th>การจัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentDocs as $doc): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <i class="bi bi-file-earmark-text text-primary me-2"></i>
                    <div class="fw-semibold document-title"><?= htmlspecialchars($doc['title']) ?></div>
                  </div>
                </td>
                <td><?= htmlspecialchars($doc['category_name'] ?? '-') ?></td>
                <td><?= thai_date($doc['created_at']) ?></td>
                <td>
                  <span class="badge <?= $doc['status'] == 'เผยแพร่' ? 'bg-success' : ($doc['status'] == 'ร่าง' ? 'bg-warning' : 'bg-secondary') ?>">
                    <?= htmlspecialchars($doc['status']) ?>
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm" role="group">
                    <?php if ($doc['file_path'] && file_exists('../../../uploads/documents/' . $doc['file_path'])): ?>
                      <a href="../../../uploads/documents/<?= $doc['file_path'] ?>" target="_blank" class="btn btn-outline-primary btn-sm" title="ดูเอกสาร" data-bs-toggle="tooltip" data-bs-placement="top">
                        <i class="bi bi-eye"></i>
                        <span class="d-none d-md-inline ms-1">ดู</span>
                      </a>
                      <a href="../../../uploads/documents/<?= $doc['file_path'] ?>" download="<?= $doc['title'] ?>" class="btn btn-outline-success btn-sm" title="ดาวน์โหลด" data-bs-toggle="tooltip" data-bs-placement="top">
                        <i class="bi bi-download"></i>
                        <span class="d-none d-md-inline ms-1">ดาวน์โหลด</span>
                      </a>
                    <?php else: ?>
                      <span class="text-muted small">ไม่มีไฟล์</span>
                    <?php endif; ?>
                    <a href="../documents/document_edit.php?id=<?= $doc['document_id'] ?>" class="btn btn-outline-warning btn-sm" title="แก้ไข" data-bs-toggle="tooltip"><i class="bi bi-pencil"></i></a>
                    <a href="../documents/document_delete.php?id=<?= $doc['document_id'] ?>" class="btn btn-outline-danger btn-sm" title="ลบ" data-bs-toggle="tooltip" onclick="return confirm('ยืนยันการลบเอกสารนี้?');"><i class="bi bi-trash"></i></a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($recentDocs)): ?>
              <tr>
                <td colspan="5" class="text-center text-muted">ไม่มีข้อมูล</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
</script>
<?php include_once '../../../includes/admin_footer.php'; ?> 