<?php
require_once __DIR__ . '/../config/database.php';

class Modal
{
  protected $db;
  protected $table;
  protected $primaryKey = 'id';

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function findById($id)
  {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function findAll()
  {
    $stmt = $this->db->query("SELECT * FROM {$this->table}");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function create($data)
  {
    $fields = array_keys($data);
    $values = array_values($data);
    $placeholders = str_repeat('?,', count($fields) - 1) . '?';

    $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") 
                VALUES ({$placeholders})";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($values);

    return $this->findById($this->db->lastInsertId());
  }

  public function update($id, $data)
  {
    $fields = array_keys($data);
    $values = array_values($data);
    $set = implode('=?,', $fields) . '=?';

    $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?";

    $stmt = $this->db->prepare($sql);
    $values[] = $id;
    $stmt->execute($values);

    return $this->findById($id);
  }

  public function delete($id)
  {
    $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
    return $stmt->execute([$id]);
  }
}
