<?php
require_once 'Modal.php';

class MajorModal extends Modal
{
  public function __construct($db)
  {
    parent::__construct($db);
    $this->table = "majors";
    $this->primaryKey = "major_id";
  }

  public function getAllMajors()
  {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY major_name ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getSubMajorsByMajorId($major_id)
  {
    $stmt = $this->db->prepare("SELECT * FROM sub_majors WHERE major_id = ? ORDER BY sub_major_name ASC");
    $stmt->execute([$major_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAllSubMajors()
  {
    $stmt = $this->db->prepare("SELECT * FROM sub_majors ORDER BY sub_major_name ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}