<?php
/**
 * 健康度量管理系統 - 資料庫連線設定檔
 * 使用 PHP PDO 方式連接 MySQL 資料庫
 */

// 資料庫設定
define('DB_HOST', 'localhost');
define('DB_NAME', 'health_management_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    public $conn;

    /**
     * 建立資料庫連線
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // 先嘗試連線到指定資料庫
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            // 如果資料庫不存在（錯誤代碼 1049），嘗試建立它
            if ($exception->getCode() == 1049) {
                try {
                    $this->createDatabaseIfNotExists();
                    // 重新嘗試連線
                    $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                } catch(PDOException $e) {
                    echo "資料庫連線錯誤: " . $e->getMessage();
                }
            } else {
                echo "資料庫連線錯誤: " . $exception->getMessage();
            }
        }

        return $this->conn;
    }
    
    /**
     * 自動建立資料庫和基本表格
     */
    private function createDatabaseIfNotExists() {
        // 連線到 MySQL（不指定資料庫）
        $dsn = "mysql:host=" . $this->host . ";charset=" . $this->charset;
        $pdo = new PDO($dsn, $this->username, $this->password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 建立資料庫
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name . " DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci");
        
        // 切換到新資料庫
        $pdo->exec("USE " . $this->db_name);
        
        // 建立基本表格結構
        $this->createBasicTables($pdo);
    }
    
    /**
     * 建立基本表格結構和測試資料
     */
    private function createBasicTables($pdo) {
        // 建立 users 表格
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                user_id INT PRIMARY KEY AUTO_INCREMENT,
                user_type ENUM('technician', 'patient') NOT NULL,
                name VARCHAR(100) NOT NULL,
                account VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                first_login BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // 建立 medical_items 表格
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS medical_items (
                item_id INT PRIMARY KEY AUTO_INCREMENT,
                item_name VARCHAR(100) NOT NULL,
                description TEXT,
                score_range_min INT DEFAULT 1,
                score_range_max INT DEFAULT 10,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // 建立 test_results 表格
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS test_results (
                result_id INT PRIMARY KEY AUTO_INCREMENT,
                patient_id INT NOT NULL,
                item_id INT NOT NULL,
                technician_id INT NOT NULL,
                score DECIMAL(3,1) NOT NULL,
                test_date DATE NOT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (item_id) REFERENCES medical_items(item_id) ON DELETE CASCADE,
                FOREIGN KEY (technician_id) REFERENCES users(user_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // 檢查是否已有測試資料
        $result = $pdo->query("SELECT COUNT(*) FROM users");
        if ($result->fetchColumn() == 0) {
            $this->insertTestData($pdo);
        }
    }
    
    /**
     * 插入測試資料
     */
    private function insertTestData($pdo) {
        // 插入醫檢員
        $pdo->exec("
            INSERT INTO users (user_type, name, account, password, first_login) VALUES
            ('technician', '張醫檢師', 'tech001', 'password123', FALSE),
            ('technician', '李醫檢師', 'tech002', 'password123', FALSE),
            ('technician', '王醫檢師', 'tech003', 'password123', FALSE)
        ");
        
        // 插入受檢者
        $pdo->exec("
            INSERT INTO users (user_type, name, account, password, first_login) VALUES
            ('patient', '陳小明', 'patient001', 'password123', TRUE),
            ('patient', '林小華', 'patient002', 'password123', TRUE),
            ('patient', '黃小美', 'patient003', 'password123', TRUE),
            ('patient', '劉小強', 'patient004', 'password123', TRUE),
            ('patient', '周小麗', 'patient005', 'password123', TRUE)
        ");
        
        // 插入醫檢項目
        $pdo->exec("
            INSERT INTO medical_items (item_name, description, score_range_min, score_range_max) VALUES
            ('血壓測量', '收縮壓與舒張壓檢測', 1, 10),
            ('血糖檢測', '空腹血糖濃度測量', 1, 10),
            ('膽固醇檢測', '總膽固醇、LDL、HDL檢測', 1, 10),
            ('心電圖檢查', '心臟電位活動記錄', 1, 10),
            ('體重指數評估', 'BMI身體質量指數計算', 1, 10)
        ");
        
        // 插入檢查結果
        $pdo->exec("
            INSERT INTO test_results (patient_id, item_id, technician_id, score, test_date, notes) VALUES
            (4, 1, 1, 8.5, '2024-09-20', '血壓正常範圍'),
            (4, 2, 1, 7.0, '2024-09-20', '血糖稍高，建議控制'),
            (5, 1, 2, 9.0, '2024-09-21', '血壓優良'),
            (5, 3, 2, 6.5, '2024-09-21', '膽固醇需要注意'),
            (6, 4, 3, 8.0, '2024-09-22', '心電圖正常')
        ");
    }
}

/**
 * 取得資料庫連線實例
 */
function getDBConnection() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * 安全的密碼雜湊
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * 驗證密碼
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * 驗證密碼格式 (英文字大小寫與數字混合之 12 碼字串)
 */
function validatePasswordFormat($password) {
    // 密碼必須是 12 碼
    if (strlen($password) !== 12) {
        return false;
    }
    
    // 必須包含大寫字母
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // 必須包含小寫字母
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    // 必須包含數字
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    // 只能包含英文字母和數字
    if (!preg_match('/^[a-zA-Z0-9]+$/', $password)) {
        return false;
    }
    
    return true;
}

/**
 * 清理輸入資料
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * 檢查使用者是否已登入
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * 檢查使用者類型
 */
function getUserType() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

/**
 * 登出使用者
 */
function logout() {
    session_unset();
    session_destroy();
}

/**
 * 重新導向
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

// 啟動 session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>