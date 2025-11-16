<?php
class Database
{
  private $host = "localhost";
  private $db_name = "student_club";
  private $username = "root";
  private $password = "";
  private $conn;

  public function __construct()
  {
    try {
      $this->conn = new PDO(
        "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
        $this->username,
        $this->password,
        array(
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false
        )
      );
    } catch (PDOException $e) {
      die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
    }
  }

  public function getConnection()
  {
    if ($this->conn === null) {
      throw new Exception("ไม่สามารถเชื่อมต่อกับฐานข้อมูลได้");
    }
    return $this->conn;
  }
}