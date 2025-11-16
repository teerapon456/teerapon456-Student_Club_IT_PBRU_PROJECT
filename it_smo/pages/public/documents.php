<?php
$pageTitle = "รายการเอกสาร - สโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ";
include_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../api/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// ตรวจสอบสิทธิ์
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? '';
$isAdmin = in_array($userRole, ['ผู้ดูแลระบบ', 'อาจารย์ที่ปรึกษา', 'นายกสโมสรนักศึกษา', 'รองนายกสโมสรนักศึกษา', 'เลขานุการสโมสรนักศึกษา']);

// ค้นหา/แบ่งหน้า
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// ดึงหมวดหมู่ทั้งหมด
$categories = $db->query("SELECT * FROM document_categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

// เงื่อนไข WHERE
$where_conditions = ["d.status = 'เผยแพร่'"];
if ($isAdmin) {
  // เห็นทุก access_level
} elseif ($isLoggedIn) {
  $where_conditions[] = "(d.access_level = 'สาธารณะ' OR d.access_level = 'ภายใน')";
} else {
  $where_conditions[] = "d.access_level = 'สาธารณะ'";
}
$params = [];

if ($search) {
  $where_conditions[] = "(d.title LIKE ? OR d.document_number LIKE ? OR d.description LIKE ? OR d.keywords LIKE ?)";
  $search_param = "%$search%";
  $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($category) {
  $where_conditions[] = "d.category_id = ?";
  $params[] = $category;
}

$where_clause = implode(" AND ", $where_conditions);

// Sorting
$order_by = "d.created_at DESC";
if ($sort === 'oldest') {
  $order_by = "d.created_at ASC";
} elseif ($sort === 'az') {
  $order_by = "d.title ASC";
} elseif ($sort === 'za') {
  $order_by = "d.title DESC";
}

// นับจำนวนเอกสาร
$count_query = "SELECT COUNT(*) as total FROM documents d WHERE $where_clause";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $per_page);

// ดึงข้อมูลเอกสาร
$query = "SELECT d.*, c.category_name, u.first_name, u.last_name 
          FROM documents d
          LEFT JOIN document_categories c ON d.category_id = c.category_id
          LEFT JOIN users u ON d.uploaded_by = u.user_id
          WHERE $where_clause
          ORDER BY $order_by
          LIMIT ? OFFSET ?";

$stmt = $db->prepare($query);
$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

function thai_date($datetime) {
  $months = [
    "", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.",
    "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."
  ];
  $time = strtotime($datetime);
  $day = date('j', $time);
  $month = $months[(int)date('n', $time)];
  $year = date('Y', $time) + 543;
  return "$day $month $year";
}
?>

<div class="documents-page">
  <!-- Hero Section -->
  <div class="hero-section">
    <div class="container">
      <div class="hero-content">
        <h1 class="hero-title">
          <i class="fas fa-file-alt"></i>
          รายการเอกสาร
        </h1>
        <p class="hero-subtitle">ค้นหาและดาวน์โหลดเอกสารต่างๆ ของสโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ</p>
      </div>
    </div>
  </div>

  <div class="container py-5">
    <!-- Search and Filter Section -->
    <div class="search-filter-section">
      <div class="search-box">
        <form method="get" class="search-form">
          <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาเอกสาร..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-search"></i> ค้นหา
            </button>
            <?php if ($search || $category || $sort !== 'newest') : ?>
              <a href="documents.php" class="btn btn-outline-secondary clear-filter-btn" title="ล้างการค้นหา">
                <i class="fas fa-times-circle"></i>
              </a>
            <?php endif; ?>
          </div>
          <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
          <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
        </form>
      </div>

      <div class="filter-controls">
        <div class="category-filters">
          <a href="?search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
             class="category-badge <?= $category == '' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> ทั้งหมด
          </a>
          <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= $cat['category_id'] ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
               class="category-badge <?= $category == $cat['category_id'] ? 'active' : '' ?>">
              <i class="fas fa-tag"></i> <?= htmlspecialchars($cat['category_name']) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <div class="sort-control">
          <select name="sort" class="form-select" onchange="this.form.submit()">
            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>ใหม่ล่าสุด</option>
            <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>เก่าสุด</option>
            <option value="az" <?= $sort == 'az' ? 'selected' : '' ?>>ชื่อ (A-Z)</option>
            <option value="za" <?= $sort == 'za' ? 'selected' : '' ?>>ชื่อ (Z-A)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Documents Grid -->
    <div class="documents-grid">
      <?php if (!empty($documents)): ?>
        <?php foreach ($documents as $doc): ?>
          <div class="document-card">
            <div class="document-header">
              <span class="category-tag">
                <i class="fas fa-tag"></i> <?= htmlspecialchars($doc['category_name']) ?>
              </span>
              <span class="date-tag">
                <i class="far fa-calendar-alt"></i> <?= thai_date($doc['created_at']) ?>
              </span>
            </div>
            <div class="document-body">
              <h3 class="document-title">
                <i class="fas fa-file-alt"></i> <?= htmlspecialchars($doc['title']) ?>
              </h3>
              <p class="document-description">
                <?= mb_strimwidth(htmlspecialchars($doc['description'] ?? ''), 0, 100, "...") ?>
              </p>
            </div>
            <div class="document-footer">
              <div class="uploader-info">
                <i class="fas fa-user-circle"></i>
                <?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?>
              </div>
              <a href="../../uploads/documents/<?= htmlspecialchars($doc['file_path']) ?>" 
                 class="download-btn" download>
                <i class="fas fa-download"></i> ดาวน์โหลด
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="no-results">
          <i class="fas fa-info-circle"></i>
          <h3>ไม่พบเอกสารที่ค้นหา</h3>
          <p>ลองปรับเงื่อนไขการค้นหาหรือเลือกหมวดหมู่อื่น</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div class="pagination-section">
        <nav aria-label="Page navigation">
          <ul class="pagination">
            <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&sort=<?= $sort ?>">
                  <i class="fas fa-chevron-left"></i>
                </a>
              </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&sort=<?= $sort ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&sort=<?= $sort ?>">
                  <i class="fas fa-chevron-right"></i>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.documents-page {
  background: #f8f9fa;
  min-height: 100vh;
}

.hero-section {
  background: linear-gradient(135deg, #4a90e2 0%, #2c3e50 100%);
  padding: 4rem 0;
  color: white;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="rgba(255,255,255,0.1)"/></svg>');
  opacity: 0.1;
}

.hero-content {
  position: relative;
  z-index: 1;
}

.hero-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.hero-title i {
  margin-right: 0.5rem;
}

.hero-subtitle {
  font-size: 1.2rem;
  opacity: 0.9;
}

.search-filter-section {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

.search-box {
  margin-bottom: 1.5rem;
}

.search-form .input-group {
  max-width: 600px;
}

.search-form .form-control {
  border-radius: 0.5rem 0 0 0.5rem;
  padding: 0.75rem 1.25rem;
  font-size: 1rem;
  border: 2px solid #e9ecef;
}

.search-form .btn {
  border-radius: 0 0.5rem 0.5rem 0;
  padding: 0.75rem 1.5rem;
}

.filter-controls {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
}

.category-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.category-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background: #f8f9fa;
  border-radius: 2rem;
  color: #495057;
  text-decoration: none;
  transition: all 0.3s ease;
  border: 1px solid #e9ecef;
}

.category-badge i {
  margin-right: 0.5rem;
}

.category-badge:hover,
.category-badge.active {
  background: #4a90e2;
  color: white;
  border-color: #4a90e2;
}

.sort-control .form-select {
  padding: 0.5rem 2rem 0.5rem 1rem;
  border-radius: 2rem;
  border: 1px solid #e9ecef;
  background-color: #f8f9fa;
  cursor: pointer;
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.document-card {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.document-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.document-header {
  padding: 1rem;
  background: #f8f9fa;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.category-tag,
.date-tag {
  font-size: 0.875rem;
  color: #6c757d;
}

.category-tag i,
.date-tag i {
  margin-right: 0.25rem;
}

.document-body {
  padding: 1.5rem;
}

.document-title {
  font-size: 1.25rem;
  margin-bottom: 1rem;
  color: #2c3e50;
}

.document-title i {
  margin-right: 0.5rem;
  color: #4a90e2;
}

.document-description {
  color: #6c757d;
  font-size: 0.95rem;
  line-height: 1.5;
  margin-bottom: 1rem;
}

.document-footer {
  padding: 1rem 1.5rem;
  background: #f8f9fa;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.uploader-info {
  font-size: 0.875rem;
  color: #6c757d;
}

.uploader-info i {
  margin-right: 0.25rem;
}

.download-btn {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background: #4a90e2;
  color: white;
  border-radius: 2rem;
  text-decoration: none;
  transition: all 0.3s ease;
}

.download-btn i {
  margin-right: 0.5rem;
}

.download-btn:hover {
  background: #357abd;
  color: white;
  transform: translateY(-2px);
}

.no-results {
  grid-column: 1 / -1;
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
}

.no-results i {
  font-size: 3rem;
  color: #6c757d;
  margin-bottom: 1rem;
}

.no-results h3 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.no-results p {
  color: #6c757d;
}

.pagination-section {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
}

.pagination {
  display: flex;
  gap: 0.5rem;
}

.page-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background: white;
  color: #4a90e2;
  border: 1px solid #e9ecef;
  transition: all 0.3s ease;
}

.page-link:hover {
  background: #4a90e2;
  color: white;
  border-color: #4a90e2;
}

.page-item.active .page-link {
  background: #4a90e2;
  color: white;
  border-color: #4a90e2;
}

.clear-filter-btn {
  border-radius: 0 0.5rem 0.5rem 0;
  border-left: 0;
  display: flex;
  align-items: center;
  padding: 0 1rem;
  color: #6c757d;
  background: #fff;
  border: 1px solid #e9ecef;
  transition: all 0.3s;
  text-decoration: none;
  height: 100%;
}
.clear-filter-btn:hover {
  background: #f8d7da;
  color: #721c24;
  border-color: #f5c6cb;
}
.clear-filter-btn i {
  margin-right: 0;
}

@media (max-width: 768px) {
  .hero-section {
    padding: 3rem 0;
  }

  .hero-title {
    font-size: 2rem;
  }

  .search-filter-section {
    padding: 1rem;
  }

  .filter-controls {
    flex-direction: column;
    align-items: stretch;
  }

  .category-filters {
    margin-bottom: 1rem;
  }

  .documents-grid {
    grid-template-columns: 1fr;
  }

  .document-card {
    margin-bottom: 1rem;
  }
}
</style>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>

<?php if ($isAdmin): ?>
  <div class="mb-3 text-end">
    <a href="../admin/documents/document_upload.php" class="btn btn-primary">
      <i class="fas fa-upload"></i> อัปโหลดเอกสาร
    </a>
  </div>
<?php endif; ?>