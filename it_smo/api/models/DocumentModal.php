<?php
require_once __DIR__ . '/Modal.php';

class DocumentModal extends Modal
{
  protected $table = 'documents';
  protected $primaryKey = 'document_id';

  public function __construct($db)
  {
    parent::__construct($db);
  }

  public function findByCategory($categoryId)
  {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function findByUser($userId)
  {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function search($query)
  {
    $searchTerm = "%{$query}%";
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE title LIKE ? OR description LIKE ?");
    $stmt->execute([$searchTerm, $searchTerm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRecentDocuments($limit = 10)
  {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAll($params = [])
  {
    // กรอง params ที่ว่างออก (เช่น '', null)
    $params = array_filter($params, function($v) {
      return $v !== '' && $v !== null;
    });
    $where = [];
    $binds = [];
    if (!empty($params['search'])) {
      $where[] = '(d.title LIKE :search OR d.description LIKE :search)';
      $binds[':search'] = '%' . $params['search'] . '%';
    }
    if (!empty($params['category'])) {
      $where[] = 'd.category_id = :category';
      $binds[':category'] = $params['category'];
    }
    if (!empty($params['status'])) {
      $where[] = 'd.status = :status';
      $binds[':status'] = $params['status'];
    }
    if (!empty($params['year'])) {
      $where[] = '(YEAR(d.created_at) = :year OR d.document_year = :year)';
      $binds[':year'] = $params['year'];
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $query = "SELECT d.*, c.category_name, u.first_name, u.last_name 
              FROM " . $this->table . " d
              LEFT JOIN document_categories c ON d.category_id = c.category_id
              LEFT JOIN users u ON d.uploaded_by = u.user_id
              $whereSql
              ORDER BY d.created_at DESC";
    $stmt = $this->db->prepare($query);
    foreach ($binds as $k => $v) {
      $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getById($id)
  {
    $query = "SELECT d.*, c.category_name, u.first_name, u.last_name 
              FROM " . $this->table . " d
              LEFT JOIN document_categories c ON d.category_id = c.category_id
              LEFT JOIN users u ON d.uploaded_by = u.user_id
              WHERE d.document_id = ?";

    $stmt = $this->db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    return $stmt->fetch();
  }

  public function getByCategory($category_id)
  {
    $query = "SELECT d.*, c.category_name, u.first_name, u.last_name 
              FROM " . $this->table . " d
              LEFT JOIN document_categories c ON d.category_id = c.category_id
              LEFT JOIN users u ON d.uploaded_by = u.user_id
              WHERE d.category_id = ?
              ORDER BY d.created_at DESC";

    $stmt = $this->db->prepare($query);
    $stmt->bindParam(1, $category_id);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function getByUploader($user_id)
  {
    $query = "SELECT d.*, c.category_name, u.first_name, u.last_name 
              FROM " . $this->table . " d
              LEFT JOIN document_categories c ON d.category_id = c.category_id
              LEFT JOIN users u ON d.uploaded_by = u.user_id
              WHERE d.uploaded_by = ?
              ORDER BY d.created_at DESC";

    $stmt = $this->db->prepare($query);
    $stmt->bindParam(1, $user_id);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function create($data)
  {
    $query = "INSERT INTO " . $this->table . "
    (document_number, title, description, file_path, file_type, file_size, 
    category_id, uploaded_by, access_level, status, publish_date, document_year, expiry_date, keywords, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->db->prepare($query);

    $stmt->bindParam(1, $data['document_number']);
    $stmt->bindParam(2, $data['title']);
    $stmt->bindParam(3, $data['description']);
    $stmt->bindParam(4, $data['file_path']);
    $stmt->bindParam(5, $data['file_type']);
    $stmt->bindParam(6, $data['file_size']);
    $stmt->bindParam(7, $data['category_id']);
    $stmt->bindParam(8, $data['uploaded_by']);
    $stmt->bindParam(9, $data['access_level']);
    $stmt->bindParam(10, $data['status']);
    $stmt->bindParam(11, $data['publish_date']);
    $stmt->bindParam(12, $data['document_year']);
    $stmt->bindParam(13, $data['expiry_date']);
    $stmt->bindParam(14, $data['keywords']);
    $stmt->bindParam(15, $data['created_at']);
    $stmt->bindParam(16, $data['updated_at']);

    return $stmt->execute();
  }

  public function update($id, $data)
  {
    $query = "UPDATE " . $this->table . "
              SET document_number = ?, title = ?, description = ?, 
                  file_path = ?, file_type = ?, file_size = ?,
                  category_id = ?, access_level = ?, status = ?,
                  publish_date = ?, document_year = ?, expiry_date = ?, keywords = ?,
                  updated_at = ?
              WHERE document_id = ?";

    $stmt = $this->db->prepare($query);

    $stmt->bindParam(1, $data['document_number']);
    $stmt->bindParam(2, $data['title']);
    $stmt->bindParam(3, $data['description']);
    $stmt->bindParam(4, $data['file_path']);
    $stmt->bindParam(5, $data['file_type']);
    $stmt->bindParam(6, $data['file_size']);
    $stmt->bindParam(7, $data['category_id']);
    $stmt->bindParam(8, $data['access_level']);
    $stmt->bindParam(9, $data['status']);
    $stmt->bindParam(10, $data['publish_date']);
    $stmt->bindParam(11, $data['document_year']);
    $stmt->bindParam(12, $data['expiry_date']);
    $stmt->bindParam(13, $data['keywords']);
    $stmt->bindParam(14, $data['updated_at']);
    $stmt->bindParam(15, $id);

    return $stmt->execute();
  }

  public function delete($id)
  {
    $query = "DELETE FROM " . $this->table . " WHERE document_id = ?";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(1, $id);
    return $stmt->execute();
  }

  public function getByAccessLevel($access_level)
  {
    $query = "SELECT d.*, c.category_name, u.first_name, u.last_name 
              FROM " . $this->table . " d
              LEFT JOIN document_categories c ON d.category_id = c.category_id
              LEFT JOIN users u ON d.uploaded_by = u.user_id
              WHERE d.access_level = ?
              ORDER BY d.created_at DESC";

    $stmt = $this->db->prepare($query);
    $stmt->bindParam(1, $access_level);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function checkPermission($documentId, $roleId)
  {
      $query = "SELECT can_view FROM document_permissions WHERE document_id = ? AND role_id = ?";
      $stmt = $this->db->prepare($query);
      $stmt->execute([$documentId, $roleId]);
      $permission = $stmt->fetch(PDO::FETCH_ASSOC);

      // คืนค่าเป็นจริงหากได้รับอนุญาต
      return ($permission && $permission['can_view']);
  }

  // ฟังก์ชันค้นหาแบบละเอียด (advance search/filter)
  public function filter($params = [])
  {
    return $this->getAll($params);
  }
}