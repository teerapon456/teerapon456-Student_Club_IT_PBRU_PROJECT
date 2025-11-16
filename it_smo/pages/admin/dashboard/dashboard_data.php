<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../../api/config/Database.php';
$database = new Database();
$pdo = $database->getConnection();

try {
    $type = $_GET['type'] ?? 'monthly';
    
    // ข้อมูลสถิติการใช้งานระบบ
    $months = [];
    $uploads = [];
    $downloads = [];
    
    if ($type === 'weekly') {
        // ข้อมูลรายสัปดาห์ (8 สัปดาห์ล่าสุด)
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = date('Y-m-d', strtotime("-$i weeks"));
            $weekEnd = date('Y-m-d', strtotime("-$i weeks +6 days"));
            $months[] = 'สัปดาห์ ' . date('W', strtotime($weekStart));
            
            // นับจำนวนการอัปโหลด
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM documents 
                WHERE DATE(created_at) BETWEEN ? AND ?
            ");
            $stmt->execute([$weekStart, $weekEnd]);
            $uploads[] = (int)$stmt->fetchColumn();
            
            // นับจำนวนการดาวน์โหลด
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM document_access_logs 
                WHERE action = 'ดาวน์โหลด' AND DATE(created_at) BETWEEN ? AND ?
            ");
            $stmt->execute([$weekStart, $weekEnd]);
            $downloads[] = (int)$stmt->fetchColumn();
        }
    } else {
        // ข้อมูลรายเดือน (6 เดือนล่าสุด)
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[] = date('M Y', strtotime("-$i months"));
            
            // นับจำนวนการอัปโหลด
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM documents 
                WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
            ");
            $stmt->execute([$date]);
            $uploads[] = (int)$stmt->fetchColumn();
            
            // นับจำนวนการดาวน์โหลด
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM document_access_logs 
                WHERE action = 'ดาวน์โหลด' AND DATE_FORMAT(created_at, '%Y-%m') = ?
            ");
            $stmt->execute([$date]);
            $downloads[] = (int)$stmt->fetchColumn();
        }
    }
    
    // ข้อมูลประเภทเอกสาร
    $stmt = $pdo->query("
        SELECT 
            COALESCE(c.category_name, 'ไม่มีหมวดหมู่') as category_name,
            COUNT(*) as count
        FROM documents d
        LEFT JOIN document_categories c ON d.category_id = c.category_id
        GROUP BY d.category_id, c.category_name
        ORDER BY count DESC
        LIMIT 8
    ");
    $docTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $docTypeLabels = [];
    $docTypeData = [];
    $colors = ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc', '#858796', '#fd7e14', '#20c997'];
    
    foreach ($docTypes as $index => $type) {
        $docTypeLabels[] = $type['category_name'];
        $docTypeData[] = (int)$type['count'];
    }
    
    // ข้อมูลสถิติเพิ่มเติม
    $stats = [
        'total_documents' => $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn(),
        'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'pending_documents' => $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'ร่าง'")->fetchColumn(),
        'today_downloads' => $pdo->query("SELECT COUNT(*) FROM document_access_logs WHERE action = 'ดาวน์โหลด' AND DATE(created_at) = CURDATE()")->fetchColumn(),
        'this_month_uploads' => $pdo->query("SELECT COUNT(*) FROM documents WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')")->fetchColumn(),
        'this_month_downloads' => $pdo->query("SELECT COUNT(*) FROM document_access_logs WHERE action = 'ดาวน์โหลด' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')")->fetchColumn(),
        'published_documents' => $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'เผยแพร่'")->fetchColumn(),
        'active_users_this_month' => $pdo->query("SELECT COUNT(DISTINCT user_id) FROM document_access_logs WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')")->fetchColumn(),
        'new_users_this_month' => $pdo->query("SELECT COUNT(*) FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')")->fetchColumn(),
        'total_downloads' => $pdo->query("SELECT COUNT(*) FROM document_access_logs WHERE action = 'ดาวน์โหลด'")->fetchColumn()
    ];
    
    // ข้อมูลกิจกรรมล่าสุด (ใช้ query แบบเดิม)
    $recentActivities = [];
    
    // กิจกรรมการอัปโหลดเอกสาร
    $uploadActivities = $pdo->query("
        SELECT 
            'upload' as type,
            d.title,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            d.created_at,
            'อัปโหลดเอกสาร' as action_text,
            d.status
        FROM documents d
        LEFT JOIN users u ON d.uploaded_by = u.user_id
        ORDER BY d.created_at DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // กิจกรรมการดาวน์โหลด
    $downloadActivities = $pdo->query("
        SELECT 
            'download' as type,
            d.title,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            dal.created_at,
            'ดาวน์โหลดเอกสาร' as action_text,
            d.status
        FROM document_access_logs dal
        LEFT JOIN documents d ON dal.document_id = d.document_id
        LEFT JOIN users u ON dal.user_id = u.user_id
        WHERE dal.action = 'ดาวน์โหลด'
        ORDER BY dal.created_at DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // กิจกรรมการเพิ่มผู้ใช้ใหม่
    $newUsers = $pdo->query("
        SELECT 
            'user' as type,
            '' as title,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            u.created_at,
            'เพิ่มผู้ใช้งานใหม่' as action_text,
            '' as status
        FROM users u
        ORDER BY u.created_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // รวมกิจกรรมและเรียงตามเวลา
    $allActivities = array_merge($uploadActivities, $downloadActivities, $newUsers);
    usort($allActivities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    $recentActivities = array_slice($allActivities, 0, 12);
    
    echo json_encode([
        'success' => true,
        'months' => $months,
        'uploads' => $uploads,
        'downloads' => $downloads,
        'docTypeLabels' => $docTypeLabels,
        'docTypes' => $docTypeData,
        'stats' => $stats,
        'recentActivities' => $recentActivities
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 