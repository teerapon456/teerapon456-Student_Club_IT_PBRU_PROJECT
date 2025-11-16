    -- สร้างฐานข้อมูลสำหรับระบบจัดการสมาชิกและเอกสารราชการ
    CREATE DATABASE IF NOT EXISTS student_club;
    USE student_club;

    -- ตารางสาขาวิชา
    CREATE TABLE IF NOT EXISTS majors (
        major_id INT(3) AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสสาขาวิชา',
        major_code VARCHAR(10) NOT NULL UNIQUE COMMENT 'ชื่อย่อสาขาวิชา',
        major_name VARCHAR(100) NOT NULL COMMENT 'ชื่อสาขาวิชา',
        description TEXT COMMENT 'คำอธิบายสาขาวิชา',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่สร้าง',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไขล่าสุด'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    CREATE INDEX idx_major_name ON majors(major_name);

    -- ตารางแขนงวิชา
    CREATE TABLE IF NOT EXISTS sub_majors (
        sub_major_id INT(3) AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสแขนงวิชา',
        sub_major_code VARCHAR(10) NOT NULL COMMENT 'รหัสย่อแขนงวิชา',
        sub_major_name VARCHAR(100) NOT NULL COMMENT 'ชื่อแขนงวิชา',
        major_id INT(3) COMMENT 'รหัสสาขาวิชา',
        description TEXT COMMENT 'คำอธิบายแขนงวิชา',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่สร้าง',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไขล่าสุด',
        FOREIGN KEY (major_id) REFERENCES majors(major_id) ON DELETE CASCADE,
        UNIQUE KEY unique_sub_major_code (sub_major_code, major_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    CREATE INDEX idx_sub_major_search ON sub_majors(sub_major_code, sub_major_name);
    
    -- ตารางบทบาท
    CREATE TABLE IF NOT EXISTS roles (
        role_id INT(2) AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสบทบาท',
        role_name VARCHAR(30) NOT NULL COMMENT 'ชื่อตำแหน่ง',
        role_description TEXT COMMENT 'คำอธิบายบทบาท',
        permissions JSON COMMENT 'สิทธิ์การใช้งานในรูปแบบ JSON',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่สร้าง',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไขล่าสุด'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    CREATE INDEX idx_role_name ON roles(role_name);

    -- ตารางผู้ใช้
    CREATE TABLE IF NOT EXISTS users (
        user_id INT(10) AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสผู้ใช้',
        student_id VARCHAR(9) UNIQUE COMMENT 'รหัสนักศึกษา',
        password VARCHAR(255) NOT NULL COMMENT 'รหัสผ่าน (เข้ารหัสแล้ว)',
        email VARCHAR(100) NOT NULL UNIQUE COMMENT 'อีเมล',
        first_name VARCHAR(50) NOT NULL COMMENT 'ชื่อ',
        last_name VARCHAR(50) NOT NULL COMMENT 'นามสกุล',
        phone VARCHAR(10) COMMENT 'เบอร์โทรศัพท์',
        role_id INT(2) COMMENT 'รหัสบทบาท',
        major_id INT(3) COMMENT 'รหัสสาขาวิชา',
        sub_major_id INT(3) COMMENT 'รหัสแขนงวิชา',
        profile_image VARCHAR(255) COMMENT 'ที่อยู่รูปโปรไฟล์',
        status ENUM('เปิดใช้งาน', 'ปิดใช้งาน', 'ระงับการใช้งาน') DEFAULT 'เปิดใช้งาน' COMMENT 'สถานะผู้ใช้',
        last_login TIMESTAMP NULL COMMENT 'บันทึกวันที่เข้าสู่ระบบล่าสุด',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่สร้างบัญชีผู้ใช้',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไขล่าสุด',
        FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL,
        FOREIGN KEY (major_id) REFERENCES majors(major_id) ON DELETE SET NULL,
        FOREIGN KEY (sub_major_id) REFERENCES sub_majors(sub_major_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    CREATE INDEX idx_user_search ON users(student_id, email, first_name, last_name);
    CREATE INDEX idx_user_major ON users(major_id, sub_major_id);

    -- ตารางประเภทเอกสาร
    CREATE TABLE IF NOT EXISTS document_categories (
        category_id INT(4) AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสประเภทเอกสาร',
        category_name VARCHAR(100) NOT NULL COMMENT 'ชื่อประเภทเอกสาร',
        description TEXT COMMENT 'คำอธิบายประเภทเอกสาร',
        parent_id INT(4) DEFAULT NULL COMMENT 'รหัสประเภทเอกสารหลัก',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่สร้างประเภทเอกสาร',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไขข้อมูลประเภทเอกสาร',
        FOREIGN KEY (parent_id) REFERENCES document_categories(category_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    CREATE INDEX idx_category_name ON document_categories(category_name);
    CREATE INDEX idx_category_parent ON document_categories(parent_id);

    -- ตารางเอกสาร
    CREATE TABLE IF NOT EXISTS documents (
        document_id INT(4) AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสเอกสาร',
        document_number VARCHAR(10) NOT NULL COMMENT 'เลขที่เอกสาร',
        title VARCHAR(255) NOT NULL COMMENT 'ชื่อเอกสาร',
        description TEXT COMMENT 'รายละเอียดเอกสาร',
        file_path VARCHAR(255) NOT NULL COMMENT 'ที่อยู่ไฟล์เอกสาร',
        file_type VARCHAR(50) COMMENT 'ประเภทของไฟล์',
        file_size INT(8) COMMENT 'ขนาดไฟล์ (ไบต์)',
        category_id INT(4) COMMENT 'รหัสประเภทเอกสาร',
        uploaded_by INT(10) COMMENT 'รหัสผู้ใช้ที่อัปโหลดไฟล์',
        access_level ENUM('สาธารณะ', 'ภายใน') DEFAULT 'ภายใน' COMMENT 'ระดับการเข้าถึงเอกสาร',
        status ENUM('ร่าง', 'เผยแพร่', 'ยกเลิก') DEFAULT 'ร่าง' COMMENT 'สถานะเอกสาร',
        publish_date DATE COMMENT 'วันที่เผยแพร่',
        document_year INT(4) COMMENT 'ปีของเอกสาร',
        keywords TEXT COMMENT 'คำสำคัญสำหรับค้นหา',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่สร้างเอกสาร',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไขข้อมูลเอกสาร',
        FOREIGN KEY (category_id) REFERENCES document_categories(category_id) ON DELETE SET NULL,
        FOREIGN KEY (uploaded_by) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE INDEX idx_document_search ON documents(document_number, title(100));
    CREATE INDEX idx_document_status ON documents(status, publish_date);
    CREATE INDEX idx_document_category ON documents(category_id);
    CREATE INDEX idx_document_uploader ON documents(uploaded_by);
    CREATE FULLTEXT INDEX idx_document_keywords ON documents(keywords);

    -- ตารางสิทธิ์การเข้าถึงเอกสาร
    CREATE TABLE IF NOT EXISTS document_permissions (
        permission_id INT(4) AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสสิทธิ์เข้าถึงเอกสาร',
        document_id INT(4) COMMENT 'รหัสเอกสาร',
        role_id INT(2) COMMENT 'รหัสบทบาท',
        can_view BOOLEAN DEFAULT FALSE COMMENT 'สิทธิ์ดูเอกสาร',
        can_download BOOLEAN DEFAULT FALSE COMMENT 'สิทธิ์ดาวน์โหลดเอกสาร',
        can_edit BOOLEAN DEFAULT FALSE COMMENT 'สิทธิ์แก้ไขเอกสาร',
        can_delete BOOLEAN DEFAULT FALSE COMMENT 'สิทธิ์ลบเอกสาร',
        can_share BOOLEAN DEFAULT FALSE COMMENT 'สิทธิ์แชร์เอกสาร',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่สร้างสิทธิ์การเข้าถึงเอกสาร',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไขสิทธิ์การเข้าถึงเอกสาร',
        FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE CASCADE,
        FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
        UNIQUE KEY unique_document_role (document_id, role_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE INDEX idx_permission_document ON document_permissions(document_id);
    CREATE INDEX idx_permission_role ON document_permissions(role_id);

    -- ตารางประวัติการเข้าถึงเอกสาร
    CREATE TABLE IF NOT EXISTS document_access_logs (
        log_id INT(10) AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสประวัติการเข้าถึงเอกสาร',
        document_id INT(4) COMMENT 'รหัสเอกสาร',
        user_id INT(10) COMMENT 'รหัสผู้ใช้',
        action ENUM('ดู', 'ดาวน์โหลด', 'แก้ไข', 'ลบ') COMMENT 'การดำเนินการระบบ',
        ip_address VARCHAR(45) COMMENT 'ชื่อไอพีที่เข้าถึงเอกสาร',
        user_agent TEXT COMMENT 'ชื่อเบราว์เซอร์ที่เปิดระบบ',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่มีการใช้สิทธิ์ารเข้าถึงเอกสาร',
        FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE INDEX idx_access_log ON document_access_logs(document_id, user_id, created_at);
    CREATE INDEX idx_access_user ON document_access_logs(user_id);
    CREATE INDEX idx_access_date ON document_access_logs(created_at);

    -- ตารางประวัติการเข้าสู่ระบบ
    CREATE TABLE IF NOT EXISTS user_login_history (
    history_id INT(10) AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสประวัติผู้เข้าใช้งานระบบ',
    user_id INT(10) COMMENT 'รหัสผู้ใช้',
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'เวลาที่ผู้ใช้เข้าสู่ระบบ',
    ip_address VARCHAR(45) COMMENT 'ชื่อไอพีที่เข้าสู่ระบบ',
    user_agent TEXT COMMENT 'ชื่อเบราว์เซอร์ที่เปิดระบบ',
    login_status ENUM('สำเร็จ', 'ล้มเหลว') DEFAULT 'สำเร็จ' COMMENT 'สถานะการเข้าใช้งาน',
    failure_reason VARCHAR(255) COMMENT 'ข้อความสาเหตุของการเข้าสู่ระบบล้มเหลว',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE INDEX idx_login_history ON user_login_history(user_id, login_time);
    CREATE INDEX idx_login_status ON user_login_history(login_status, login_time);

    -- ตารางบันทึกการใช้งานระบบ (กิจกรรมผู้ใช้ทั่วไประดับระบบ)
    CREATE TABLE IF NOT EXISTS user_activity_logs (
        log_id INT(10) AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสประวัติการใช้งาน',
        user_id INT(10) NOT NULL COMMENT 'รหัสผู้ใช้',
        action_type VARCHAR(50) NOT NULL COMMENT 'ประเภทการกระทำ (login, logout, view_document, download_document, upload_document, edit_document, delete_document, search, profile_edit, password_change ฯลฯ)',
        description TEXT COMMENT 'คำอธิบายรายละเอียดของกิจกรรม',
        ip_address VARCHAR(45) COMMENT 'ที่อยู่ IP ของผู้ใช้',
        user_agent TEXT COMMENT 'ข้อมูลเบราว์เซอร์/อุปกรณ์',
        document_id INT(4) NULL COMMENT 'รหัสเอกสาร (ถ้ามี)',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่และเวลาที่เกิดกิจกรรม',
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE INDEX idx_user_activity ON user_activity_logs(user_id, created_at);
    CREATE INDEX idx_activity_type ON user_activity_logs(action_type);
    CREATE INDEX idx_activity_date ON user_activity_logs(created_at);
    CREATE INDEX idx_activity_document ON user_activity_logs(document_id);