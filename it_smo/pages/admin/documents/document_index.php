<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "จัดการข้อมูลเอกสาร | IT SMO";
$pageGroup = 'documents';

require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';

// เรียกใช้ Auth ผ่าน getInstance
$auth = Auth::getInstance();
$user = $auth->getCurrentUser();

$allowedRoles = ['ผู้ดูแลระบบ', 'นายกสโมสรนักศึกษา','อาจารย์ที่ปรึกษา','รองนายกสโมสรนักศึกษา','เลขานุการสโมสรนักศึกษา'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
  header('Location: /it_smo/pages/error/403.php');
  exit();
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// รับค่าจาก GET
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Pagination
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

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

// Sorting
$allowedSort = ['title', 'category_name', 'status', 'created_at'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'asc' : 'desc';

// Main query
$sql = "SELECT d.*, c.category_name FROM documents d LEFT JOIN document_categories c ON d.category_id = c.category_id $whereSql ORDER BY $sort $order LIMIT ?, ?";
$paramsForQuery = $params;
$paramsForQuery[] = $offset;
$paramsForQuery[] = $perPage;
$stmt = $db->prepare($sql);
$stmt->execute($paramsForQuery);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total for pagination
$countSql = "SELECT COUNT(*) FROM documents d LEFT JOIN document_categories c ON d.category_id = c.category_id $whereSql";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

// Get categories for filter
$catStmt = $db->query("SELECT category_id, category_name FROM document_categories ORDER BY category_name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Get years for filter
$yearStmt = $db->query("SELECT DISTINCT YEAR(created_at) AS year FROM documents ORDER BY year DESC");
$years = $yearStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <?php if (isset($_GET['delete_success'])): ?>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script>
      Swal.fire({
        icon: 'success',
        title: 'สำเร็จ!',
        text: 'ลบเอกสารสำเร็จแล้ว',
        confirmButtonText: 'ตกลง'
      });
      </script>
      <?php endif; ?>
      <?php if (isset($_GET['delete_error'])): ?>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script>
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: '<?= htmlspecialchars($_GET['delete_error']) ?>',
        confirmButtonText: 'ตกลง'
      });
      </script>
      <?php endif; ?>
      <?php if (isset($_GET['edit_success'])): ?>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script>
      Swal.fire({
        icon: 'success',
        title: 'สำเร็จ!',
        text: 'แก้ไขข้อมูลเอกสารสำเร็จแล้ว',
        confirmButtonText: 'ตกลง'
      });
      </script>
      <?php endif; ?>
      <?php if (isset($_GET['upload_success'])): ?>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script>
      Swal.fire({
        icon: 'success',
        title: 'สำเร็จ!',
        text: 'อัปโหลดเอกสารสำเร็จแล้ว',
        confirmButtonText: 'ตกลง'
      });
      </script>
      <?php endif; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-alt me-2"></i>จัดการข้อมูลเอกสาร</h2>
        <div class="d-flex gap-2">
          <a href="document_upload.php" class="btn btn-primary">
            <i class="fas fa-upload"></i> อัปโหลดเอกสาร
          </a>
        </div>
      </div>

      <!-- Search and Filter -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <form method="GET" class="row g-3">
            <div class="col-md-3">
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="search" placeholder="ค้นหาชื่อเอกสาร, คำอธิบาย"
                  value="<?= htmlspecialchars($search) ?>">
              </div>
            </div>
            <div class="col-md-2">
              <select class="form-select" name="category">
                <option value="">ทุกหมวดหมู่</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>" <?= $category == $cat['category_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['category_name']) ?>
                </option>
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
              <select class="form-select" name="year">
                <option value="">ทุกปี</option>
                <?php foreach ($years as $y): ?>
                <option value="<?= $y['year'] ?>" <?= $year == $y['year'] ? 'selected' : '' ?>><?= $y['year'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                  <i class="fas fa-filter me-1"></i> ค้นหา
                </button>
                <?php if (!empty($search) || !empty($category) || !empty($status) || !empty($year)): ?>
                <a href="document_index.php" class="btn btn-secondary">
                  <i class="fas fa-times"></i>
                </a>
                <?php endif; ?>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Documents Table -->
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>ชื่อเอกสาร</th>
                  <th>หมวดหมู่</th>
                  <th>ระดับการเข้าถึง</th>
                  <th>สถานะ</th>
                  <th>วันที่อัปโหลด</th>
                  <th class="text-center">การดำเนินการ</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($documents)): ?>
                <tr>
                  <td colspan="6" class="text-center py-4">
                    <div class="text-muted">
                      <i class="fas fa-search fa-2x mb-2"></i>
                      <p class="mb-0">ไม่พบข้อมูลเอกสาร</p>
                    </div>
                  </td>
                </tr>
                <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <i class="fas fa-file-alt text-primary me-2"></i>
                      <?= htmlspecialchars($doc['title']) ?>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-info">
                      <?= htmlspecialchars($doc['category_name']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($doc['access_level']) ?></td>
                  <td>
                    <span class="badge bg-<?= getStatusBadgeColor($doc['status']) ?>">
                      <?= getStatusText($doc['status']) ?>
                    </span>
                  </td>
                  <td><?= date('d/m/Y', strtotime($doc['created_at'])) ?></td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                      <?php
                        $filePath = isset($doc['file_path']) ? $doc['file_path'] : '';
                        $fileExists = $filePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $filePath);
                      ?>
                      <?php if ($fileExists): ?>
                        <a href="<?= htmlspecialchars($filePath) ?>" class="btn btn-sm btn-info" target="_blank" title="เปิดไฟล์เอกสาร">
                          <i class="fas fa-eye"></i>
                        </a>
                      <?php else: ?>
                        <a href="#" class="btn btn-sm btn-secondary disabled" tabindex="-1" aria-disabled="true" title="ไม่มีไฟล์ให้เปิด">
                          <i class="fas fa-eye"></i>
                        </a>
                      <?php endif; ?>
                      <a href="document_edit.php?id=<?= $doc['document_id'] ?>" class="btn btn-sm btn-warning" title="แก้ไข">
                        <i class="fas fa-edit"></i>
                      </a>
                      <!-- ปุ่มลบและ Modal ยืนยัน -->
                      <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $doc['document_id'] ?>" title="ลบ">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                      <!-- Modal ยืนยันการลบ -->
                      <div class="modal fade" id="deleteModal<?= $doc['document_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $doc['document_id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="deleteModalLabel<?= $doc['document_id'] ?>">ยืนยันการลบเอกสาร</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <p>คุณแน่ใจหรือไม่ว่าต้องการลบเอกสาร <strong><?= htmlspecialchars($doc['title']) ?></strong> นี้?</p>
                            </div>
                            <div class="modal-footer">
                              <form method="post" action="document_delete.php">
                                <input type="hidden" name="document_id" value="<?= $doc['document_id'] ?>">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                <button type="submit" class="btn btn-danger">ลบ</button>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
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
                  href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>&year=<?= urlencode($year) ?>">
                  <i class="fas fa-chevron-left"></i>
                </a>
              </li>

              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                <a class="page-link"
                  href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>&year=<?= urlencode($year) ?>">
                  <?= $i ?>
                </a>
              </li>
              <?php endfor; ?>
              <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link"
                  href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>&year=<?= urlencode($year) ?>">
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

<?php
// Helper functions
function getStatusBadgeColor($status)
{
  $colors = [
    'เผยแพร่' => 'success',
    'ร่าง' => 'warning',
    'ยกเลิก' => 'secondary'
  ];
  return $colors[$status] ?? 'secondary';
}

function getStatusText($status)
{
  $texts = [
    'เผยแพร่' => 'เผยแพร่',
    'ร่าง' => 'ร่าง',
    'ยกเลิก' => 'ยกเลิก'
  ];
  return $texts[$status] ?? 'ไม่ระบุ';
}

function getSortUrl($column)
{
  global $sort, $order, $search, $category, $status, $year;
  $newOrder = ($sort === $column && $order === 'asc') ? 'desc' : 'asc';
  $params = [
    'sort' => $column,
    'order' => $newOrder,
    'search' => $search,
    'category' => $category,
    'status' => $status,
    'year' => $year
  ];
  return '?' . http_build_query($params);
}

function getSortIcon($column)
{
  global $sort, $order;
  if ($sort !== $column) {
    return '<i class="fas fa-sort"></i>';
  }
  return $order === 'asc' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
}

include '../../../includes/admin_footer.php';
?>