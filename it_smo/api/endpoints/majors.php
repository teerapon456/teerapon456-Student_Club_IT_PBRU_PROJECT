<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/MajorModal.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $majorModel = new MajorModal($db);

    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['major_id'])) {
                // ดึงข้อมูลแขนงวิชาตาม major_id
                $majorId = (int)$_GET['major_id'];
                if ($majorId > 0) {
                    $subMajors = $majorModel->getSubMajorsByMajorId($majorId);
                    echo json_encode([
                        'success' => true, 
                        'data' => $subMajors,
                        'message' => 'ดึงข้อมูลแขนงวิชาสำเร็จ'
                    ]);
                } else {
                    throw new Exception("Major ID ไม่ถูกต้อง");
                }
            } else {
                // ดึงข้อมูลสาขาวิชาทั้งหมด
                $majors = $majorModel->getAllMajors();
                echo json_encode([
                    'success' => true, 
                    'data' => $majors,
                    'message' => 'ดึงข้อมูลสาขาวิชาสำเร็จ'
                ]);
            }
            break;
            
        default:
            throw new Exception("Method ไม่รองรับ");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'error' => true
    ]);
} 