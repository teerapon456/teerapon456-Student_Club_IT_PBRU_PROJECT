<?php
require_once 'Modal.php';

class RoleModal extends Modal
{
  public function __construct($db)
  {
    parent::__construct($db);
    $this->table = "roles";
    $this->primaryKey = "role_id";
  }

  public function getAllRoles()
  {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY role_name ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAllRolesSorted($sort = 'role_id', $order = 'asc')
  {
    $allowedSort = ['role_id', 'role_name', 'created_at'];
    $allowedOrder = ['asc', 'desc'];
    $sort = in_array($sort, $allowedSort) ? $sort : 'role_id';
    $order = in_array(strtolower($order), $allowedOrder) ? strtoupper($order) : 'ASC';
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY $sort $order");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRoleById($role_id)
  {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
    $stmt->execute([$role_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
