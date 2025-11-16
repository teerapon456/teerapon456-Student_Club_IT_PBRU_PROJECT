<?php
require_once 'Modal.php';

class UserModal extends Modal
{
  public function __construct($db)
  {
    $this->db = $db;
    $this->table = "users";
    $this->primaryKey = "user_id";
  }

  public function getByEmail($email)
  {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getByStudentId($student_id)
  {
    $stmt = $this->db->prepare("SELECT u.*, r.role_name FROM {$this->table} u LEFT JOIN roles r ON u.role_id = r.role_id WHERE u.student_id = ?");
    $stmt->execute([$student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function updateLastLogin($user_id)
  {
    $stmt = $this->db->prepare("UPDATE {$this->table} SET last_login = CURRENT_TIMESTAMP WHERE {$this->primaryKey} = ?");
    return $stmt->execute([$user_id]);
  }

  public function getAll($params = [])
  {
    $page = isset($params['page']) ? (int)$params['page'] : 1;
    $perPage = 10; // จำนวนรายการต่อหน้า
    $offset = ($page - 1) * $perPage;

    $where = [];
    $values = [];

    // จัดการการค้นหา
    if (!empty($params['search'])) {
      $search = "%{$params['search']}%";
      $where[] = "(u.student_id LIKE ? 
        OR u.email LIKE ? 
        OR u.first_name LIKE ? 
        OR u.last_name LIKE ? 
        OR u.phone LIKE ? 
        OR m.major_name LIKE ?)";
      $values = array_merge($values, [$search, $search, $search, $search, $search, $search]);
    }

    // จัดการตัวกรองบทบาท
    if (!empty($params['role'])) {
      $where[] = "r.role_name = ?";
      $values[] = $params['role'];
    }

    // จัดการตัวกรองสถานะ
    if (!empty($params['status'])) {
      $where[] = "u.status = ?";
      $values[] = $params['status'];
    }

    // Filter by major_id
    if (!empty($params['major_id'])) {
      $where[] = "u.major_id = ?";
      $values[] = $params['major_id'];
    }
    // Filter by sub_major_id
    if (!empty($params['sub_major_id'])) {
      $where[] = "u.sub_major_id = ?";
      $values[] = $params['sub_major_id'];
    }

    // สร้างส่วนคำสั่ง WHERE
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    // จัดการการเรียงลำดับ
    $sort = isset($params['sort']) ? $params['sort'] : 'created_at';
    $order = isset($params['order']) ? strtoupper($params['order']) : 'DESC';

    // ตรวจสอบความถูกต้องของคอลัมน์การเรียงลำดับ
    $allowedSortColumns = ['student_id', 'first_name', 'email', 'role_name', 'status', 'created_at'];
    if (!in_array($sort, $allowedSortColumns)) {
      $sort = 'created_at';
    }

    // ตรวจสอบคำสั่งซื้อ
    if (!in_array($order, ['ASC', 'DESC'])) {
      $order = 'DESC';
    }

    // รับจำนวนรวมสำหรับการแบ่งหน้า
    $countQuery = "SELECT COUNT(*) FROM {$this->table} u 
                  LEFT JOIN roles r ON u.role_id = r.role_id 
                  LEFT JOIN majors m ON u.major_id = m.major_id 
                  {$whereClause}";
    $countStmt = $this->db->prepare($countQuery);
    $countStmt->execute($values);
    $total = $countStmt->fetchColumn();

    // รับข้อมูลแบ่งหน้าด้วยการเรียงลำดับ
    $query = "SELECT u.*, r.role_name, m.major_name 
              FROM {$this->table} u 
              LEFT JOIN roles r ON u.role_id = r.role_id 
              LEFT JOIN majors m ON u.major_id = m.major_id 
              {$whereClause} 
              ORDER BY {$sort} {$order} 
              LIMIT ? OFFSET ?";

    $stmt = $this->db->prepare($query);
    $values[] = $perPage;
    $values[] = $offset;
    $stmt->execute($values);

    return [
      'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
      'total' => $total,
      'total_pages' => ceil($total / $perPage),
      'current_page' => $page
    ];
  }

  public function getById($id)
  {
    $stmt = $this->db->prepare("SELECT u.*, r.role_name FROM {$this->table} u LEFT JOIN roles r ON u.role_id = r.role_id WHERE u.{$this->primaryKey} = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function create($data)
  {
    try {
      $stmt = $this->db->prepare("INSERT INTO {$this->table} (student_id, password, email, first_name, last_name, phone, role_id, major_id, sub_major_id, profile_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([
        $data['student_id'] ?? null,
        $data['password'],
        $data['email'],
        $data['first_name'],
        $data['last_name'],
        $data['phone'],
        $data['role_id'],
        $data['major_id'] ?? null,
        $data['sub_major_id'] ?? null,
        $data['profile_image'] ?? null,
        $data['status']
      ]);
      return true;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function update($id, $data)
  {
    $fields = [];
    $params = [];

    // ไม่อนุญาตให้แก้ student_id
    if (isset($data['email'])) { $fields[] = 'email=?'; $params[] = $data['email']; }
    if (isset($data['first_name'])) { $fields[] = 'first_name=?'; $params[] = $data['first_name']; }
    if (isset($data['last_name'])) { $fields[] = 'last_name=?'; $params[] = $data['last_name']; }
    if (isset($data['phone'])) { $fields[] = 'phone=?'; $params[] = $data['phone']; }
    if (isset($data['role_id'])) { $fields[] = 'role_id=?'; $params[] = $data['role_id']; }
    if (isset($data['major_id'])) { $fields[] = 'major_id=?'; $params[] = $data['major_id']; }
    if (isset($data['sub_major_id'])) { $fields[] = 'sub_major_id=?'; $params[] = $data['sub_major_id']; }
    if (isset($data['profile_image'])) { $fields[] = 'profile_image=?'; $params[] = $data['profile_image']; }
    if (isset($data['status'])) { $fields[] = 'status=?'; $params[] = $data['status']; }
    if (isset($data['password'])) { $fields[] = 'password=?'; $params[] = $data['password']; }

    if (empty($fields)) return false;

    $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey}=?";
    $params[] = $id;

    try {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return true; // คืน true ถ้า execute สำเร็จ ไม่ต้องเช็ค rowCount
    } catch (PDOException $e) {
        error_log('User update error: ' . $e->getMessage());
        return false;
    }
}

  public function updatePassword($user_id, $password)
  {
    $stmt = $this->db->prepare("UPDATE {$this->table} SET password = ? WHERE {$this->primaryKey} = ?");
    return $stmt->execute([$password, $user_id]);
  }

  public function updateProfileImage($user_id, $profile_image)
  {
    $stmt = $this->db->prepare("UPDATE {$this->table} SET profile_image = ? WHERE {$this->primaryKey} = ?");
    return $stmt->execute([$profile_image, $user_id]);
  }

  public function delete($id)
  {
    $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
    return $stmt->execute([$id]);
  }

  public function softDelete($id)
  {
    $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'ระงับการใช้งาน' WHERE {$this->primaryKey} = ?");
    return $stmt->execute([$id]);
  }

  public function getRoles()
  {
    $stmt = $this->db->prepare("SELECT * FROM roles ORDER BY role_name");
    $stmt->execute();
    return [
      'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ];
  }

  public function getConnection() {
    return $this->db;
  }
}