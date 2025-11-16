<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "รายงานสถิติภาพรวม | IT SMO";
$pageGroup = 'reports';
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch summaries for the overview
// Total Users
$stmtUsers = $db->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $stmtUsers->fetch(PDO::FETCH_ASSOC)['total_users'];
// Total Documents
$stmtDocs = $db->query("SELECT COUNT(*) as total_documents FROM documents");
$totalDocuments = $stmtDocs->fetch(PDO::FETCH_ASSOC)['total_documents'];
// Total Public Documents
$stmtPublicDocs = $db->query("SELECT COUNT(*) as total_public FROM documents WHERE access_level = 'สาธารณะ' AND status = 'เผยแพร่'");
$totalPublicDocs = $stmtPublicDocs->fetch(PDO::FETCH_ASSOC)['total_public'];
// Total Internal Documents
$stmtInternalDocs = $db->query("SELECT COUNT(*) as total_internal FROM documents WHERE access_level = 'ภายใน' AND status = 'เผยแพร่'");
$totalInternalDocs = $stmtInternalDocs->fetch(PDO::FETCH_ASSOC)['total_internal'];

?>

<div class="container-fluid py-4">
  <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>รายงานสถิติภาพรวม</h2>

  <div class="row g-4 mb-4">
    <div class="col-md-3">
      <div class="card text-center shadow-sm border-0">
        <div class="card-body">
          <div class="mb-2 text-primary"><i class="fas fa-users fa-2x"></i></div>
          <h3 class="card-title mb-1"><?= htmlspecialchars($totalUsers) ?></h3>
          <div class="text-muted mb-2">ผู้ใช้งานทั้งหมด</div>
          <a href="report_users.php" class="btn btn-outline-primary btn-sm">ดูรายงานผู้ใช้งาน</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center shadow-sm border-0">
        <div class="card-body">
          <div class="mb-2 text-info"><i class="fas fa-file-alt fa-2x"></i></div>
          <h3 class="card-title mb-1"><?= htmlspecialchars($totalDocuments) ?></h3>
          <div class="text-muted mb-2">เอกสารทั้งหมด</div>
          <a href="report_documents.php" class="btn btn-outline-info btn-sm">ดูรายงานเอกสาร</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center shadow-sm border-0">
        <div class="card-body">
          <div class="mb-2 text-success"><i class="fas fa-globe-asia fa-2x"></i></div>
          <h3 class="card-title mb-1"><?= htmlspecialchars($totalPublicDocs) ?></h3>
          <div class="text-muted mb-2">เอกสารสาธารณะ</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center shadow-sm border-0">
        <div class="card-body">
          <div class="mb-2 text-warning"><i class="fas fa-lock fa-2x"></i></div>
          <h3 class="card-title mb-1"><?= htmlspecialchars($totalInternalDocs) ?></h3>
          <div class="text-muted mb-2">เอกสารภายใน</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Placeholder for future charts/statistics -->
  <div class="row mt-4">
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold"><i class="fas fa-chart-pie me-2"></i>สัดส่วนเอกสาร (Placeholder)</div>
        <div class="card-body text-center text-muted">
          <span>กราฟแสดงสัดส่วนเอกสาร (Public/Internal) จะมาเร็ว ๆ นี้</span>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold"><i class="fas fa-user-friends me-2"></i>สถิติผู้ใช้งาน (Placeholder)</div>
        <div class="card-body text-center text-muted">
          <span>กราฟแสดงสถิติผู้ใช้งานจะแสดงที่นี่ในอนาคต</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../../../includes/admin_footer.php'; ?> 