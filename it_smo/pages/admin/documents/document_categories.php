<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
$pageTitle = "จัดการหมวดหมู่เอกสาร | IT SMO";
require_once '../../../includes/auth.php';
require_once '../../../includes/admin_header.php';
require_once '../../../api/config/Database.php';

// Database functions remain the same as your original code
function getAllCategories() {
    $database = new Database();
    $db = $database->getConnection();
    $sql = "SELECT c.category_id AS id, c.category_name AS name, c.description, COUNT(d.document_id) AS document_count
            FROM document_categories c
            LEFT JOIN documents d ON d.category_id = c.category_id
            GROUP BY c.category_id, c.category_name, c.description
            ORDER BY c.category_name";
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addCategory($categoryData) {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("INSERT INTO document_categories (category_name, description) VALUES (?, ?)");
    return $stmt->execute([$categoryData['name'], $categoryData['description']]);
}

function updateCategory($categoryId, $categoryData) {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("UPDATE document_categories SET category_name = ?, description = ? WHERE category_id = ?");
    return $stmt->execute([$categoryData['name'], $categoryData['description'], $categoryId]);
}

function deleteCategory($categoryId) {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("DELETE FROM document_categories WHERE category_id = ?");
    return $stmt->execute([$categoryId]);
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $categoryData = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];

        $errors = [];
        if (empty($categoryData['name'])) {
            $errors[] = "ชื่อหมวดหมู่ต้องระบุ";
        }

        if (empty($errors)) {
            if (addCategory($categoryData)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Category added successfully'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to add category'];
            }
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    } 
    elseif (isset($_POST['update_category'])) {
        $categoryId = $_POST['category_id'] ?? '';
        $categoryData = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];

        $errors = [];
        if (empty($categoryId)) {
            $errors[] = "รหัสหมวดหมู่ต้องระบุ";
        }
        if (empty($categoryData['name'])) {
            $errors[] = "ชื่อหมวดหมู่ต้องระบุ";
        }

        if (empty($errors)) {
            if (updateCategory($categoryId, $categoryData)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Category updated successfully'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to update category'];
            }
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    } 
    elseif (isset($_POST['delete_category'])) {
        $categoryId = $_POST['category_id'] ?? '';
        if (deleteCategory($categoryId)) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Category deleted successfully'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to delete category'];
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$categories = getAllCategories();
?>

<!-- Modern Styled Content -->
<div class="container-fluid px-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold mb-0">
      <i class="fas fa-folder-open me-2 text-primary"></i>จัดการข้อมูลหมวดหมู่เอกสาร
    </h1> 
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex gap-2">
          <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-folder"></i> เพิ่มหมวดหมู่เอกสาร
          </a>
          <a href="document_index.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> ย้อนกลับ
          </a>
        </div>
      </div>
  </div>

  <!-- Flash Messages -->
  <?php if (isset($_SESSION['flash_message'])): ?>
  <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show shadow-sm" role="alert">
    <?= $_SESSION['flash_message']['message'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php unset($_SESSION['flash_message']); ?>
  <?php endif; ?>

  <!-- Main Card -->
  <div class="card shadow-sm border-0">
    <div class="card-body p-4">
      <?php if (empty($categories)): ?>
      <div class="text-center py-5">
        <i class="fas fa-folder-open fa-4x text-muted mb-4"></i>
        <h4 class="text-muted">ไม่พบหมวดหมู่เอกสาร</h4>
        <p class="text-muted">เริ่มต้นโดยเพิ่มหมวดหมู่เอกสารแรก</p>
        <button type="button" class="btn btn-secondary rounded-pill mt-3" data-bs-toggle="modal"
          data-bs-target="./document_index.php">
          <i class="fas fa-arrow-left"></i>กลับ
        </button>
        <button type="button" class="btn btn-primary rounded-pill mt-3" data-bs-toggle="modal"
          data-bs-target="#addCategoryModal">
          <i class="fas fa-plus me-2"></i>เพิ่มหมวดหมู่เอกสาร
        </button>
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="bg-light">
            <tr>
              <th class="ps-4">ชื่อหมวดหมู่</th>
              <th>รายละเอียด</th>
              <th class="text-center">จำนวนเอกสาร</th>
              <th class="text-end pe-4">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $category): ?>
            <tr class="border-top">
              <td class="ps-4">
                <div class="d-flex align-items-center">
                  <i class="fas fa-folder text-warning me-3 fs-4"></i>
                  <div>
                    <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($category['name']) ?></h6>
                  </div>
                </div>
              </td>
              <td>
                <p class="mb-0 text-muted"><?= htmlspecialchars($category['description']) ?: 'No description' ?></p>
              </td>
              <td class="text-center">
                <span class="badge bg-primary px-3 py-2">
                  <?= $category['document_count'] ?? 0 ?> ฉบับ
                </span>
              </td>
              <td class="text-end pe-4">
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-primary rounded-start-pill" data-bs-toggle="modal"
                    data-bs-target="#editCategoryModal<?= $category['id'] ?>">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-danger rounded-end-pill" data-bs-toggle="modal"
                    data-bs-target="#deleteCategoryModal<?= $category['id'] ?>">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editCategoryModal<?= $category['id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                      <i class="fas fa-edit me-2"></i>แก้ไขหมวดหมู่เอกสาร
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                      aria-label="Close"></button>
                  </div>
                  <form method="POST">
                    <div class="modal-body">
                      <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                      <div class="mb-3">
                        <label class="form-label">ชื่อหมวดหมู่</label>
                        <input type="text" class="form-control" name="name"
                          value="<?= htmlspecialchars($category['name']) ?>" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea class="form-control" name="description"
                          rows="3"><?= htmlspecialchars($category['description']) ?></textarea>
                      </div>
                    </div>
                    <div class="modal-footer border-0">
                      <button type="button" class="btn btn-secondary rounded-pill"
                        data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" name="update_category" class="btn btn-primary rounded-pill">
                        <i class="fas fa-save me-2"></i>บันทึกการแก้ไข
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteCategoryModal<?= $category['id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                      <i class="fas fa-exclamation-triangle me-2"></i>ยืนยันการลบ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                      aria-label="Close"></button>
                  </div>
                  <form method="POST">
                    <div class="modal-body">
                      <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                      <div class="text-center mb-4">
                        <i class="fas fa-trash-alt fa-4x text-danger mb-3"></i>
                        <h5>คุณต้องการลบหมวดหมู่เอกสารนี้หรือไม่?</h5>
                        <p class="text-muted">หมวดหมู่: <strong><?= htmlspecialchars($category['name']) ?></strong></p>
                        <p class="text-danger small">
                          การลบหมวดหมู่เอกสารนี้ไม่สามารถยกเลิกได้และจะลบหมวดหมู่เอกสารนี้อย่างถาวร</p>
                      </div>
                    </div>
                    <div class="modal-footer border-0">
                      <button type="button" class="btn btn-secondary rounded-pill"
                        data-bs-dismiss="modal">ยกเลิก</button>
                      <button type="submit" name="delete_category" class="btn btn-danger rounded-pill">
                        <i class="fas fa-trash-alt me-2"></i>ลบอย่างถาวร
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-plus me-2"></i>เพิ่มหมวดหมู่เอกสาร
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">ชื่อหมวดหมู่</label>
            <input type="text" class="form-control" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">รายละเอียด</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" name="add_category" class="btn btn-primary rounded-pill">
            <i class="fas fa-plus me-2"></i>เพิ่มหมวดหมู่
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../../includes/admin_footer.php'; ?>