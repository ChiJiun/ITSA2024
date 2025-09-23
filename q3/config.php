<?php
/**
 * ==============================================================================
 * 健康度量管理系統 - 資料庫連線設定檔
 * ==============================================================================
 *
 * 檔案功能：
 * 提供資料庫連線、自動初始化、錯誤處理等核心資料庫服務
 *
 * 主要特色：
 * 1. PDO 資料庫連線：使用 PHP Data Objects 進行安全的資料庫操作
 * 2. 自動資料庫建立：系統首次執行時自動建立資料庫
 * 3. 完整錯誤處理：包含連線失敗、資料庫不存在等各種情況
 * 4. UTF-8 編碼支援：完整支援中文等多國語言
 * 5. 安全性設定：包含 SQL 注入防護等安全機制
 *
 * 技術架構：
 * - PHP PDO：資料庫抽象層，支援多種資料庫
 * - MySQL：關聯式資料庫管理系統
 * - 物件導向設計：清晰的程式結構
 *
 * 開發資訊：
 * 團隊：Team 021
 * 日期：2024/09/22
 * 版本：1.0
 * ==============================================================================
 */

/* =============================================================================
   資料庫連線配置 (Database Connection Configuration)
   ============================================================================= */

/**
 * 資料庫主機位址 (Database Host)
 * 定義：MySQL 伺服器的位址
 * 預設：localhost（本機伺服器）
 */
define('DB_HOST', 'localhost');

/**
 * 資料庫名稱 (Database Name)
 * 定義：健康管理系統專用的資料庫名稱
 * 命名規則：小寫字母加底線，語義清晰
 */
define('DB_NAME', 'health_management_system');

/**
 * 資料庫使用者名稱 (Database Username)
 * 定義：連接資料庫使用的帳號
 * 安全性：建議在正式環境中使用專用帳號而非 root
 */
define('DB_USER', 'root');

/**
 * 資料庫密碼 (Database Password)
 * 定義：資料庫帳號的密碼
 * 安全性：正式環境中應設定強密碼
 */
define('DB_PASS', '');

/**
 * 資料庫字符集 (Database Charset)
 * 定義：資料庫連線使用的字符編碼
 * utf8mb4：完整的 UTF-8 支援，包含 emoji 等特殊字符
 */
define('DB_CHARSET', 'utf8mb4');

/* =============================================================================
   資料庫連線類別 (Database Connection Class)
   ============================================================================= */

/**
 * 資料庫連線管理類別
 * 功能：提供資料庫連線、初始化、錯誤處理等服務
 * 設計模式：單例模式概念，確保連線的一致性
 */
class Database {
    /** @var string 資料庫主機位址 */
    private $host = DB_HOST;
    
    /** @var string 資料庫名稱 */
    private $db_name = DB_NAME;
    
    /** @var string 資料庫使用者名稱 */
    private $username = DB_USER;
    
    /** @var string 資料庫密碼 */
    private $password = DB_PASS;
    
    /** @var string 資料庫字符集 */
    private $charset = DB_CHARSET;
    
    /** @var PDO|null PDO 連線物件，公開屬性供外部存取 */
    public $conn;

    /**
     * 建立資料庫連線方法
     * 功能：建立並配置 PDO 資料庫連線
     * 特色：自動重試、錯誤處理、安全配置
     * 
     * @return PDO|null 返回 PDO 連線物件或 null
     */
    public function getConnection() {
        // 初始化連線物件為 null
        $this->conn = null;

        try {
            /* 
             * 建立 PDO 資料來源名稱 (Data Source Name, DSN)
             * 格式：mysql:host=主機;dbname=資料庫名;charset=字符集
             * 目的：告訴 PDO 如何連接到 MySQL 資料庫
             */
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            /*
             * PDO 連線選項配置 (PDO Connection Options)
             * 目的：設定連線行為、錯誤處理、安全性等
             */
            $options = [
                // 錯誤模式：拋出例外，便於錯誤處理和調試
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                
                // 預設抓取模式：關聯陣列，便於資料存取
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                
                // 禁用模擬預處理：提升安全性，使用真正的預處理語句
                PDO::ATTR_EMULATE_PREPARES => false,
                
                // MySQL 初始化命令：確保連線使用正確的字符集
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                
                // 使用緩衝查詢：提升查詢效能
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ];

            /*
             * 建立 PDO 連線
             * 參數：DSN、使用者名稱、密碼、選項陣列
             * 成功：$this->conn 包含有效的 PDO 物件
             */
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            /*
             * 錯誤處理：資料庫不存在的情況
             * 錯誤代碼 1049：Unknown database（未知資料庫）
             * 策略：嘗試自動建立資料庫並重新連線
             */
            if ($exception->getCode() == 1049) {
                try {
                    // 呼叫自動建立資料庫方法
                    $this->createDatabaseIfNotExists();
                    
                    // 重新嘗試連線到新建立的資料庫
                    $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                } catch(PDOException $e) {
                    // 如果建立資料庫也失敗，顯示錯誤訊息
                    echo "資料庫連線錯誤: " . $e->getMessage();
                }
            } else {
                /*
                 * 其他類型的連線錯誤處理
                 * 包含：帳號密碼錯誤、主機無法連線等
                 */
                echo "資料庫連線錯誤: " . $exception->getMessage();
            }
        }

        // 返回連線物件（成功時為 PDO 物件，失敗時為 null）
        return $this->conn;
    }
    
    /**
     * 自動建立資料庫方法
     * 功能：當資料庫不存在時，自動建立資料庫和基礎表格
     * 特色：完全自動化，無需手動建立資料庫
     * 
     * 執行步驟：
     * 1. 連線到 MySQL 伺服器（不指定資料庫）
     * 2. 建立新資料庫
     * 3. 切換到新資料庫
     * 4. 建立所有必要的表格結構
     * 5. 插入測試資料
     * 
     * @throws PDOException 當資料庫建立失敗時拋出例外
     */
    private function createDatabaseIfNotExists() {
        /*
         * 建立不指定資料庫的 DSN
         * 目的：連線到 MySQL 伺服器以建立新資料庫
         */
        $dsn = "mysql:host=" . $this->host . ";charset=" . $this->charset;
        $pdo = new PDO($dsn, $this->username, $this->password);
        
        // 設定錯誤模式為例外拋出
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        /*
         * 建立資料庫 SQL 命令
         * IF NOT EXISTS：如果資料庫已存在則不會產生錯誤
         * DEFAULT CHARSET：設定預設字符集為 utf8mb4
         * COLLATE：設定排序規則，支援多國語言
         */
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name . " DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci");
        
        // 切換到新建立的資料庫
        $pdo->exec("USE " . $this->db_name);
        
        // 呼叫建立表格方法
        $this->createBasicTables($pdo);
    }
    
    /**
     * 建立基本表格結構和測試資料方法
     * 功能：建立健康管理系統所需的所有資料表
     * 特色：完整的資料表結構、外鍵約束、測試資料
     * 
     * 資料表架構：
     * 1. users：使用者資料表（醫檢員和受檢者）
     * 2. examination_items：檢測項目資料表
     * 3. examination_results：檢測結果資料表
     * 
     * @param PDO $pdo 已連線的 PDO 物件
     */
    private function createBasicTables($pdo) {
        /*
         * 建立 users 使用者資料表
         * 功能：儲存系統中所有使用者的基本資訊
         * 
         * 欄位說明：
         * - user_id：主鍵，自動遞增
         * - user_type：使用者類型（technician=醫檢員, patient=受檢者）
         * - name：使用者姓名
         * - account：登入帳號，唯一值
         * - password：登入密碼
         * - first_login：是否首次登入（用於強制變更密碼）
         * - created_at：建立時間戳記
         */
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
        
        /*
         * 建立 medical_items 醫檢項目資料表
         * 功能：儲存系統中所有可進行的醫療檢測項目
         * 
         * 欄位說明：
         * - item_id：主鍵，自動遞增
         * - item_name：檢測項目名稱
         * - description：項目詳細描述
         * - score_range_min：分數範圍最小值
         * - score_range_max：分數範圍最大值
         * - created_at：建立時間戳記
         */
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
        
        /*
         * 建立 test_results 檢測結果資料表
         * 功能：儲存所有檢測結果和相關資訊
         * 
         * 欄位說明：
         * - result_id：主鍵，自動遞增
         * - patient_id：受檢者 ID（外鍵連結 users 表）
         * - item_id：檢測項目 ID（外鍵連結 medical_items 表）
         * - technician_id：醫檢員 ID（外鍵連結 users 表）
         * - score：檢測分數（小數點一位）
         * - test_date：檢測日期
         * - notes：檢測備註
         * - created_at：建立時間戳記
         * 
         * 外鍵約束：
         * - 確保資料完整性，防止孤立記錄
         * - CASCADE 刪除：當主記錄被刪除時，相關記錄也會被刪除
         */
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
        
        /*
         * 檢查是否需要插入測試資料
         * 策略：只有在 users 表為空時才插入測試資料
         * 避免：重複執行時產生重複資料
         */
        $result = $pdo->query("SELECT COUNT(*) FROM users");
        if ($result->fetchColumn() == 0) {
            $this->insertTestData($pdo);
        }
    }
    
    /**
     * 插入測試資料方法
     * 功能：為系統提供初始的測試資料
     * 目的：讓系統在安裝後立即可用於測試
     * 
     * 資料內容：
     * 1. 3 位醫檢員帳號（tech001-003）
     * 2. 5 位受檢者帳號（patient001-005）
     * 3. 5 種醫檢項目（血壓、血糖、膽固醇等）
     * 4. 5 筆檢測結果範例
     * 
     * @param PDO $pdo 已連線的 PDO 物件
     */
    private function insertTestData($pdo) {
        /*
         * 插入醫檢員測試帳號
         * 帳號格式：tech001, tech002, tech003
         * 密碼：統一為 password123（測試用）
         * first_login：設為 FALSE，表示已變更過密碼
         */
        $pdo->exec("
            INSERT INTO users (user_type, name, account, password, first_login) VALUES
            ('technician', '張醫檢師', 'tech001', 'password123', FALSE),
            ('technician', '李醫檢師', 'tech002', 'password123', FALSE),
            ('technician', '王醫檢師', 'tech003', 'password123', FALSE)
        ");
        
        /*
         * 插入受檢者測試帳號
         * 帳號格式：patient001-005
         * 密碼：統一為 password123（測試用）
         * first_login：設為 TRUE，模擬首次登入需變更密碼
         */
        $pdo->exec("
            INSERT INTO users (user_type, name, account, password, first_login) VALUES
            ('patient', '陳小明', 'patient001', 'password123', TRUE),
            ('patient', '林小華', 'patient002', 'password123', TRUE),
            ('patient', '黃小美', 'patient003', 'password123', TRUE),
            ('patient', '劉小強', 'patient004', 'password123', TRUE),
            ('patient', '周小麗', 'patient005', 'password123', TRUE)
        ");
        
        /*
         * 插入醫檢項目測試資料
         * 涵蓋常見的健康檢測項目
         * 分數範圍：統一設為 1-10 分
         */
        $pdo->exec("
            INSERT INTO medical_items (item_name, description, score_range_min, score_range_max) VALUES
            ('血壓測量', '收縮壓與舒張壓檢測', 1, 10),
            ('血糖檢測', '空腹血糖濃度測量', 1, 10),
            ('膽固醇檢測', '總膽固醇、LDL、HDL檢測', 1, 10),
            ('心電圖檢查', '心臟電位活動記錄', 1, 10),
            ('體重指數評估', 'BMI身體質量指數計算', 1, 10)
        ");
        
        /*
         * 插入檢測結果測試資料
         * 目的：提供系統功能展示的範例資料
         * 涵蓋：不同受檢者、不同項目、不同醫檢員
         */
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

/* =============================================================================
   全域工具函數 (Global Utility Functions)
   ============================================================================= */

/**
 * 取得資料庫連線實例函數
 * 功能：提供簡便的資料庫連線方法
 * 用途：在其他 PHP 檔案中快速取得資料庫連線
 * 
 * 使用範例：
 * $conn = getDBConnection();
 * $stmt = $conn->prepare("SELECT * FROM users");
 * 
 * @return PDO|null 返回 PDO 連線物件或 null
 */
function getDBConnection() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * 安全密碼雜湊函數
 * 功能：將明文密碼轉換為安全的雜湊值
 * 演算法：使用 PHP 內建的 PASSWORD_DEFAULT（目前為 bcrypt）
 * 安全性：自動處理鹽值（salt），防止彩虹表攻擊
 * 
 * @param string $password 明文密碼
 * @return string 雜湊後的密碼
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * 密碼驗證函數
 * 功能：驗證明文密碼是否與雜湊值相符
 * 安全性：使用時間安全的比較方法，防止時序攻擊
 * 
 * @param string $password 明文密碼
 * @param string $hash 資料庫中儲存的雜湊值
 * @return bool 密碼是否正確
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * 驗證密碼格式函數
 * 功能：檢查密碼是否符合系統要求的格式
 * 規則：英文字大小寫與數字混合之 12 碼字串
 * 
 * 驗證條件：
 * 1. 長度必須為 12 字元
 * 2. 必須包含至少一個大寫字母 (A-Z)
 * 3. 必須包含至少一個小寫字母 (a-z)
 * 4. 必須包含至少一個數字 (0-9)
 * 5. 只能包含英文字母和數字
 * 
 * @param string $password 要驗證的密碼
 * @return bool 密碼格式是否正確
 */
function validatePasswordFormat($password) {
    // 檢查長度：必須是 12 碼
    if (strlen($password) !== 12) {
        return false;
    }
    
    // 檢查大寫字母：至少包含一個 A-Z
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // 檢查小寫字母：至少包含一個 a-z
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
 * 清理輸入資料函數
 * 功能：對使用者輸入的資料進行安全性清理
 * 用途：防止 XSS 攻擊、移除多餘空白、處理跳脫字元
 * 
 * 處理步驟：
 * 1. trim() - 移除字串開頭和結尾的空白字元
 * 2. stripslashes() - 移除反斜線跳脫字元
 * 3. htmlspecialchars() - 將特殊字元轉換為 HTML 實體
 * 
 * 安全性：
 * - 防止 XSS 跨站指令攻擊
 * - 清理不必要的格式字元
 * - 確保資料儲存和顯示的一致性
 * 
 * @param string $data 需要清理的原始資料
 * @return string 清理後的安全資料
 */
function cleanInput($data) {
    $data = trim($data);                    // 移除前後空白
    $data = stripslashes($data);           // 移除跳脫字元
    $data = htmlspecialchars($data);       // 轉換特殊字元為 HTML 實體
    return $data;
}

/**
 * 檢查使用者是否已登入函數
 * 功能：驗證當前使用者是否處於已登入狀態
 * 用途：權限控制、頁面存取限制、功能使用驗證
 * 
 * 檢查條件：
 * 1. SESSION 中存在 user_id 變數
 * 2. user_id 變數不為空值
 * 
 * 回傳值：
 * - true：使用者已登入，可以存取需要認證的功能
 * - false：使用者未登入，需要重新導向至登入頁面
 * 
 * 使用場景：
 * - 頁面存取控制
 * - API 功能驗證
 * - 敏感資料存取前檢查
 * 
 * @return bool 是否已登入
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * 取得使用者類型函數
 * 功能：取得當前登入使用者的類型
 * 類型：technician（醫檢員）或 patient（受檢者）
 * 
 * @return string|null 使用者類型或 null（未登入）
 */
function getUserType() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

/**
 * 使用者登出函數
 * 功能：清除使用者 SESSION 並結束會話
 * 安全性：完全清除所有會話資料
 * 
 * 執行步驟：
 * 1. session_unset() - 清除所有 SESSION 變數
 * 2. session_destroy() - 銷毀 SESSION
 */
function logout() {
    session_unset();    // 清除 SESSION 變數
    session_destroy();  // 銷毀 SESSION
}

/**
 * 頁面重新導向函數
 * 功能：將使用者導向到指定的 URL
 * 用途：登入後導向、權限檢查後導向等
 * 
 * @param string $url 目標 URL
 */
function redirect($url) {
    header("Location: $url");
    exit();  // 確保程式執行停止
}

/* =============================================================================
   SESSION 初始化 (Session Initialization)
   ============================================================================= */

/**
 * 自動啟動 SESSION 機制
 * 功能：確保在任何使用此檔案的地方都有可用的 SESSION
 * 安全性：避免重複啟動 SESSION 造成的錯誤
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * ==============================================================================
 * config.php 檔案結束註記
 * ==============================================================================
 * 
 * 總結：
 * 本檔案為健康度量管理系統的核心配置檔案，提供了完整的資料庫管理、
 * 安全性控制、會話管理等基礎功能。
 * 
 * 核心功能：
 * 1. 資料庫連線管理：PDO 連線、錯誤處理、自動重試
 * 2. 資料庫自動初始化：建立資料庫、表格、測試資料
 * 3. 安全性工具：密碼雜湊、輸入清理、格式驗證
 * 4. 會話管理：登入狀態、使用者類型、登出機制
 * 5. 工具函數：重新導向、資料清理等
 * 
 * 技術特色：
 * - 完全自動化的資料庫初始化
 * - 強大的錯誤處理和恢復機制
 * - 安全的密碼處理和驗證系統
 * - 清晰的程式碼結構和完整註解
 * ==============================================================================
 */
?>