<?php
$pageTitle = 'หน้าดาวน์โหลดเอกสาร | IT SMO';
include_once '../../includes/student_header.php';
require_once '../../api/config/Database.php';
$database = new Database();
$db = $database->getConnection();

// ดึงหมวดหมู่เอกสารทั้งหมด
$catStmt = $db->prepare("SELECT * FROM document_categories ORDER BY category_name ASC");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// รับค่าค้นหาและ filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// สร้าง SQL และ parameter
$sql = "
  SELECT d.*, dc.category_name, u.first_name, u.last_name
  FROM documents d
  LEFT JOIN document_categories dc ON d.category_id = dc.category_id
  LEFT JOIN users u ON d.uploaded_by = u.user_id
  WHERE d.status = 'เผยแพร่'
    AND d.access_level = 'สาธารณะ'
";
$params = [];

if ($search !== '') {
  $sql .= " AND (
    d.title LIKE :search 
    OR d.description LIKE :search 
    OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search
    OR dc.category_name LIKE :search
  )";
  $params['search'] = "%$search%";
}

if ($category !== '' && $category !== 'all') {
  $sql .= " AND d.category_id = :category_id";
  $params['category_id'] = $category;
}

$sql .= " ORDER BY d.created_at DESC";

try {
  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log("Error in documents.php: " . $e->getMessage());
  $documents = [];
}
?>
<div class="container py-5">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <!-- ฟอร์มค้นหาและ filter -->
          <form class="row g-2 mb-3" method="get">
            <div class="col-md-4">
              <input type="text" class="form-control" name="search" placeholder="ค้นหาเอกสาร..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
              <select class="form-select" name="category">
                <option value="all">ทุกหมวดหมู่</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['category_id'] ?>" <?= $category == $cat['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['category_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> ค้นหา</button>
            </div>
          </form>
          <!-- จบฟอร์มค้นหา -->
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light text-center">
                <tr>
                  <th>ชื่อเอกสาร</th>
                  <th>ประเภท</th>
                  <th>ผู้อัปโหลด</th>
                  <th>วันที่</th>
                  <th>ขนาด</th>
                  <th>การเข้าถึง</th>
                  <th>ดาวน์โหลด</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($documents)): foreach ($documents as $doc): ?>
                  <tr>
                    <td><?= htmlspecialchars($doc['title'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($doc['category_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars(($doc['first_name'] ?? '') . ' ' . ($doc['last_name'] ?? '')) ?></td>
                    <td><?= isset($doc['created_at']) ? date('d/m/Y', strtotime($doc['created_at'])) : '-' ?></td>
                    <td><?= isset($doc['file_size']) ? number_format($doc['file_size'] / 1024, 2) . ' KB' : '-' ?></td>
                    <td><span class="badge <?= ($doc['access_level'] ?? '') == 'สาธารณะ' ? 'bg-success' : 'bg-warning' ?>"><?= htmlspecialchars($doc['access_level'] ?? '-') ?></span></td>
                    <td>
                      <a href="<?= htmlspecialchars($doc['file_path']) ?>" class="btn btn-sm btn-info" target="_blank">
                        <i class="fas fa-eye"></i> ดูตัวอย่าง
                      </a>
                      <a href="<?= htmlspecialchars($doc['file_path']) ?>" class="btn btn-sm btn-primary" download target="_blank">
                        <i class="fas fa-download"></i> ดาวน์โหลด
                      </a>
                    </td>
                  </tr>
                <?php endforeach; else: ?>
                  <tr><td colspan="7" class="text-center text-muted">ไม่พบเอกสาร</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include_once '../../includes/student_footer.php'; ?> 