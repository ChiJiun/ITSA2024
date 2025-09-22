-- 健康度量管理系統資料庫結構
-- 設定字符編碼為 UTF-8
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
-- 建立資料庫
CREATE DATABASE IF NOT EXISTS health_management_system DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
USE health_management_system;
-- 1. 人員資料表 (包含醫檢員與受檢者)
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    user_type ENUM('technician', 'patient') NOT NULL COMMENT '使用者類型：醫檢員或受檢者',
    name VARCHAR(100) NOT NULL COMMENT '姓名',
    account VARCHAR(50) UNIQUE NOT NULL COMMENT '帳號',
    password VARCHAR(255) NOT NULL COMMENT '密碼(已加密)',
    first_login BOOLEAN DEFAULT TRUE COMMENT '是否為第一次登入',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '人員資料表';
-- 2. 醫檢項目資料表
CREATE TABLE IF NOT EXISTS medical_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    item_name VARCHAR(100) NOT NULL COMMENT '醫檢項目名稱',
    description TEXT COMMENT '項目描述',
    score_range_min INT DEFAULT 1 COMMENT '度量分數最小值',
    score_range_max INT DEFAULT 10 COMMENT '度量分數最大值',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '醫檢項目資料表';
-- 3. 檢查結果資料表
CREATE TABLE IF NOT EXISTS test_results (
    result_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL COMMENT '受檢者ID',
    item_id INT NOT NULL COMMENT '醫檢項目ID',
    score INT NOT NULL COMMENT '度量分數(1-10)',
    test_date DATE NOT NULL COMMENT '檢查日期',
    technician_id INT NOT NULL COMMENT '負責醫檢員ID',
    notes TEXT COMMENT '備註',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES medical_items(item_id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_patient_item (patient_id, item_id) COMMENT '確保每個受檢者每項檢查項目不重複'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '檢查結果資料表';
-- 建立索引以提升查詢效能
CREATE INDEX idx_users_account ON users(account);
CREATE INDEX idx_users_type ON users(user_type);
CREATE INDEX idx_test_results_patient ON test_results(patient_id);
CREATE INDEX idx_test_results_item ON test_results(item_id);
CREATE INDEX idx_test_results_date ON test_results(test_date);
-- 插入測試資料
-- 1. 插入醫檢員資料 (至少3位)
-- 密碼統一為 password123
INSERT INTO users (user_type, name, account, password, first_login)
VALUES (
        'technician',
        '張醫檢師',
        'tech001',
        'password123',
        FALSE
    ),
    (
        'technician',
        '李醫檢師',
        'tech002',
        'password123',
        FALSE
    ),
    (
        'technician',
        '王醫檢師',
        'tech003',
        'password123',
        FALSE
    );
-- 2. 插入受檢者資料 (至少5位)
INSERT INTO users (user_type, name, account, password, first_login)
VALUES (
        'patient',
        '陳小明',
        'patient001',
        'password123',
        TRUE
    ),
    (
        'patient',
        '林小華',
        'patient002',
        'password123',
        TRUE
    ),
    (
        'patient',
        '黃小美',
        'patient003',
        'password123',
        TRUE
    ),
    -- password123
    (
        'patient',
        '劉小強',
        'patient004',
        'password123',
        TRUE
    ),
    -- password123
    (
        'patient',
        '周小麗',
        'patient005',
        'password123',
        TRUE
    );
        'patient005',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        TRUE
    );
-- password123
-- 3. 插入醫檢項目資料 (至少5項)
INSERT INTO medical_items (
        item_name,
        description,
        score_range_min,
        score_range_max
    )
VALUES ('血壓測量', '收縮壓與舒張壓檢測', 1, 10),
    ('血糖檢測', '空腹血糖濃度測量', 1, 10),
    ('膽固醇檢測', '總膽固醇、LDL、HDL檢測', 1, 10),
    ('心電圖檢查', '心臟電位活動記錄', 1, 10),
    ('體重指數評估', 'BMI身體質量指數計算', 1, 10);
-- 4. 插入檢查結果資料 (模擬測試資料)
INSERT INTO test_results (
        patient_id,
        item_id,
        score,
        test_date,
        technician_id,
        notes
    )
VALUES -- 陳小明的檢查結果
    (4, 1, 8, '2024-09-20', 1, '血壓正常範圍'),
    (4, 2, 7, '2024-09-20', 1, '血糖稍高，建議控制飲食'),
    (4, 3, 6, '2024-09-20', 2, '膽固醇偏高'),
    (4, 4, 9, '2024-09-20', 2, '心電圖正常'),
    (4, 5, 5, '2024-09-20', 3, 'BMI過重，需要減重'),
    -- 林小華的檢查結果
    (5, 1, 9, '2024-09-21', 1, '血壓優良'),
    (5, 2, 8, '2024-09-21', 2, '血糖正常'),
    (5, 3, 7, '2024-09-21', 1, '膽固醇正常'),
    (5, 4, 8, '2024-09-21', 3, '心電圖正常'),
    (5, 5, 8, '2024-09-21', 2, 'BMI正常'),
    -- 黃小美的檢查結果
    (6, 1, 6, '2024-09-21', 2, '血壓稍低'),
    (6, 2, 9, '2024-09-21', 1, '血糖正常'),
    (6, 3, 8, '2024-09-21', 3, '膽固醇正常'),
    (6, 4, 7, '2024-09-21', 1, '心電圖輕微不規律'),
    (6, 5, 9, '2024-09-21', 2, 'BMI標準'),
    -- 劉小強的檢查結果
    (7, 1, 4, '2024-09-22', 3, '血壓偏高，需要追蹤'),
    (7, 2, 5, '2024-09-22', 1, '血糖偏高'),
    (7, 3, 4, '2024-09-22', 2, '膽固醇過高，需要治療'),
    (7, 4, 6, '2024-09-22', 3, '心電圖異常'),
    (7, 5, 3, '2024-09-22', 1, 'BMI過重'),
    -- 周小麗的檢查結果
    (8, 1, 10, '2024-09-22', 2, '血壓完美'),
    (8, 2, 10, '2024-09-22', 3, '血糖優良'),
    (8, 3, 9, '2024-09-22', 1, '膽固醇正常'),
    (8, 4, 10, '2024-09-22', 2, '心電圖完美'),
    (8, 5, 9, '2024-09-22', 3, 'BMI標準');
SET FOREIGN_KEY_CHECKS = 1;
-- 顯示建立的資料表
SHOW TABLES;
-- 查看資料表結構
DESCRIBE users;
DESCRIBE medical_items;
DESCRIBE test_results;
-- 測試查詢：顯示所有測試資料
SELECT u1.name AS patient_name,
    mi.item_name,
    tr.score,
    tr.test_date,
    u2.name AS technician_name
FROM test_results tr
    JOIN users u1 ON tr.patient_id = u1.user_id
    AND u1.user_type = 'patient'
    JOIN medical_items mi ON tr.item_id = mi.item_id
    JOIN users u2 ON tr.technician_id = u2.user_id
    AND u2.user_type = 'technician'
ORDER BY tr.test_date DESC,
    u1.name,
    mi.item_name;