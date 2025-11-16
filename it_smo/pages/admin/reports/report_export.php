<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../includes/auth.php';
require_once '../../../api/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// ตรวจสอบว่ามีการส่งพารามิเตอร์ type มาหรือไม่
if (!isset($_GET['type'])) {
    header('Location: /it_smo/pages/admin/reports/report_index.php?error=missing_type');
    exit();
}

$reportType = $_GET['type'];
$exportFormat = $_GET['format'] ?? 'csv'; // Default to CSV

$data = [];
$filename = '';
$headers = [];

// ตรวจสอบและดึงข้อมูลตามประเภทรายงาน
switch ($reportType) {
    case 'users':
        $stmt = $db->query("SELECT u.*, r.role_name, m.major_name 
                           FROM users u 
                           LEFT JOIN roles r ON u.role_id = r.role_id 
                           LEFT JOIN majors m ON u.major_id = m.major_id 
                           ORDER BY u.created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = 'users_report_' . date('Ymd_His');
        $headers = [
            'user_id' => 'รหัสผู้ใช้',
            'student_id' => 'รหัสนักศึกษา',
            'first_name' => 'ชื่อ',
            'last_name' => 'นามสกุล',
            'email' => 'อีเมล',
            'phone' => 'เบอร์โทรศัพท์',
            'role_name' => 'บทบาท',
            'major_name' => 'สาขาวิชา',
            'status' => 'สถานะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข'
        ];
        break;

    case 'documents':
        // รับ filter
        $search = $_GET['search'] ?? '';
        $year = $_GET['year'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(d.title LIKE :search OR d.description LIKE :search)';
            $params[':search'] = "%$search%";
        }
        if ($year !== '') {
            $where[] = '(YEAR(d.created_at) = :year OR d.document_year = :year)';
            $params[':year'] = $year;
        }
        if ($category !== '') {
            $where[] = 'd.category_id = :category';
            $params[':category'] = $category;
        }
        if ($status !== '') {
            $where[] = 'd.status = :status';
            $params[':status'] = $status;
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $sql = "SELECT d.*, c.category_name, u.first_name, u.last_name 
                FROM documents d 
                LEFT JOIN document_categories c ON d.category_id = c.category_id 
                LEFT JOIN users u ON d.uploaded_by = u.user_id 
                $whereSql
                ORDER BY d.created_at DESC";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = 'documents_report_' . date('Ymd_His');
        $headers = [
            'document_id' => 'รหัสเอกสาร',
            'title' => 'ชื่อเอกสาร',
            'description' => 'รายละเอียด',
            'file_type' => 'ประเภทไฟล์',
            'file_size' => 'ขนาดไฟล์',
            'category_name' => 'หมวดหมู่',
            'uploader_name' => 'ผู้อัปโหลด',
            'status' => 'สถานะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข'
        ];
        // Transform data to include uploader name
        $transformedData = [];
        foreach($data as $row) {
            $row['uploader_name'] = $row['first_name'] . ' ' . $row['last_name'];
            unset($row['first_name'], $row['last_name']);
            $transformedData[] = $row;
        }
        $data = $transformedData;
        break;

    case 'activities':
        $stmt = $db->query("SELECT a.*, c.category_name, u.first_name, u.last_name 
                           FROM activities a 
                           LEFT JOIN activity_categories c ON a.category_id = c.category_id 
                           LEFT JOIN users u ON a.created_by = u.user_id 
                           ORDER BY a.created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = 'activities_report_' . date('Ymd_His');
        $headers = [
            'activity_id' => 'รหัสกิจกรรม',
            'title' => 'ชื่อกิจกรรม',
            'description' => 'รายละเอียด',
            'start_date' => 'วันที่เริ่ม',
            'end_date' => 'วันที่สิ้นสุด',
            'location' => 'สถานที่',
            'category_name' => 'หมวดหมู่',
            'organizer_name' => 'ผู้จัด',
            'status' => 'สถานะ',
            'created_at' => 'วันที่สร้าง'
        ];
        // Transform data to include organizer name
        $transformedData = [];
        foreach($data as $row) {
            $row['organizer_name'] = $row['first_name'] . ' ' . $row['last_name'];
            unset($row['first_name'], $row['last_name']);
            $transformedData[] = $row;
        }
        $data = $transformedData;
        break;

    case 'attendance':
        $stmt = $db->query("SELECT a.*, u.first_name, u.last_name, act.title as activity_title 
                           FROM attendance a 
                           LEFT JOIN users u ON a.user_id = u.user_id 
                           LEFT JOIN activities act ON a.activity_id = act.activity_id 
                           ORDER BY a.created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = 'attendance_report_' . date('Ymd_His');
        $headers = [
            'attendance_id' => 'รหัสการเข้าร่วม',
            'activity_title' => 'ชื่อกิจกรรม',
            'student_name' => 'ชื่อนักศึกษา',
            'check_in' => 'เวลาลงทะเบียน',
            'check_out' => 'เวลาออก',
            'status' => 'สถานะ',
            'created_at' => 'วันที่สร้าง'
        ];
        // Transform data to include student name
        $transformedData = [];
        foreach($data as $row) {
            $row['student_name'] = $row['first_name'] . ' ' . $row['last_name'];
            unset($row['first_name'], $row['last_name']);
            $transformedData[] = $row;
        }
        $data = $transformedData;
        break;

    default:
        header('Location: /it_smo/pages/admin/reports/report_index.php?error=invalid_type');
        exit();
}

if (empty($data)) {
    header('Location: /it_smo/pages/admin/reports/report_index.php?error=no_data');
    exit();
}

// ส่งออกข้อมูลตามรูปแบบที่เลือก
if ($exportFormat === 'csv') {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

    // Output CSV data
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 in Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Output headers
    fputcsv($output, array_values($headers));

    // Output data rows
    foreach ($data as $row) {
        $rowData = [];
        foreach(array_keys($headers) as $key) {
            $rowData[] = $row[$key] ?? '';
        }
        fputcsv($output, $rowData);
    }

    fclose($output);

} elseif ($exportFormat === 'excel') {
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');

    // Output HTML table with Excel formatting
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<table border="1">';

    // Output table headers
    echo '<thead><tr>';
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr></thead>';

    // Output table data rows
    echo '<tbody>';
    foreach ($data as $row) {
        echo '<tr>';
        foreach(array_keys($headers) as $key) {
            echo '<td>' . htmlspecialchars($row[$key] ?? '') . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</body></html>';

} else {
    header('Location: /it_smo/pages/admin/reports/report_index.php?error=invalid_format');
    exit();
}

exit();
?> 