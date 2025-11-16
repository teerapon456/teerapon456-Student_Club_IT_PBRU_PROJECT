<?php
require_once __DIR__ . '/../config/Database.php';

class DocumentCategoryModal
{
  private $conn;
  private $table_name = "document_categories";

  public function __construct()
  {
    $database = new Database();
    $this->conn = $database->getConnection();
  }

  public function getAll()
  {
    $query = "SELECT * FROM " . $this->table_name . " ORDER BY category_name ASC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getById($id)
  {
    $query = "SELECT * FROM " . $this->table_name . " WHERE category_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function create($data)
  {
    $query = "INSERT INTO " . $this->table_name . " (category_name, description) VALUES (?, ?)";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $data['category_name']);
    $stmt->bindParam(2, $data['description']);
    return $stmt->execute();
  }

  public function update($id, $data)
  {
    $query = "UPDATE " . $this->table_name . " SET category_name = ?, description = ? WHERE category_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $data['category_name']);
    $stmt->bindParam(2, $data['description']);
    $stmt->bindParam(3, $id);
    return $stmt->execute();
  }

  public function delete($id)
  {
    $query = "DELETE FROM " . $this->table_name . " WHERE category_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $id);
    return $stmt->execute();
  }
}
