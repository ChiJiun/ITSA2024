<?php
/**
 * ==============================================================================
 * 健康度量管理系統 - 醫檢員管理面板
 * ==============================================================================
 *
 * 檔案功能：
 * 提供醫檢員完整的系統管理功能，包括使用者管理、檢查項目管理、檢查結果管理
 *
 * 主要功能：
 * 1. 使用者管理：新增、查詢、修改、刪除使用者資料
 * 2. 檢查項目管理：管理各種健康檢查項目的設定
 * 3. 檢查結果管理：輸入、修改、查詢檢查結果
 * 4. 權限控制：確保只有醫檢員能存取管理功能
 *
 * API 設計：
 * - 接收 POST 請求：確保資料安全傳輸
 * - JSON 回應格式：統一的前後端資料交換格式
 * - 模組化設計：每個功能獨立的處理函數
 *
 * 安全特性：
 * - 身分驗證：確保只有醫檢員能存取
 * - 輸入資料驗證：防止惡意資料輸入
 * - SQL 注入防護：使用 PDO 預處理語句
 * - 錯誤處理：完整的錯誤回饋機制
 *
 * 開發資訊：
 * 團隊：Team 021
 * 日期：2024/09/22
 * 版本：1.0
 * ==============================================================================
 */

// 引入資料庫配置和工具函數
require_once 'config.php';

/* =============================================================================
   身分驗證與權限控制 (Authentication and Authorization)
   ============================================================================= */

/**
 * 檢查使用者身分與權限
 * 功能：確保只有已登入的醫檢員能存取管理面板
 * 安全性：防止未授權存取敏感的管理功能
 * 
 * 驗證項目：
 * 1. 使用者是否已登入 (SESSION 檢查)
 * 2. 使用者類型是否為醫檢員 (technician)
 * 
 * 失敗處理：重新導向至登入頁面
 */
if (!isLoggedIn() || getUserType() !== 'technician') {
    redirect('team021.html');
}

/* =============================================================================
   HTTP 回應設定 (HTTP Response Configuration)
   ============================================================================= */

/**
 * 設定回應標頭
 * Content-Type: 指定回應格式為 JSON，字符編碼為 UTF-8
 * 目的：確保前端能正確解析 JSON 回應，支援中文等多國語言
 */
header('Content-Type: application/json; charset=utf-8');

/* =============================================================================
   請求方法驗證 (Request Method Validation)
   ============================================================================= */

/**
 * 限制請求方法為 POST
 * 安全性：POST 方法提供較好的資料安全性
 * 目的：防止 GET 請求洩露敏感資訊或造成意外的資料異動
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '僅支援 POST 請求']);
    exit();
}

/* =============================================================================
   請求路由處理 (Request Routing)
   ============================================================================= */

/**
 * 取得請求的操作類型
 * 使用 null coalescing operator (??) 提供預設值，避免未定義錯誤
 */
$action = $_POST['action'] ?? '';

/**
 * 根據操作類型路由到對應的處理函數
 * 模組化設計：每個功能分別處理，便於維護和擴展
 * 
 * 支援的操作類別：
 * 1. 使用者管理：get_users, add_user, update_user, delete_user
 * 2. 檢查項目管理：get_medical_items, add_medical_item, update_medical_item, delete_medical_item  
 * 3. 檢查結果管理：get_test_results, add_test_result, update_test_result, delete_test_result
 */

switch ($action) {
    // 使用者管理功能 (User Management)
    case 'get_users':
        getUsers();
        break;
    case 'add_user':
        addUser();
        break;
    case 'update_user':
        updateUser();
        break;
    case 'delete_user':
        deleteUser();
        break;
    
    // 檢查項目管理功能 (Medical Items Management)
    case 'get_medical_items':
        getMedicalItems();
        break;
    case 'add_medical_item':
        addMedicalItem();
        break;
    case 'update_medical_item':
        updateMedicalItem();
        break;
    case 'delete_medical_item':
        deleteMedicalItem();
        break;
    
    // 檢查結果管理功能 (Test Results Management)
    case 'get_test_results':
        getTestResults();
        break;
    case 'add_test_result':
        addTestResult();
        break;
    case 'update_test_result':
        updateTestResult();
        break;
    case 'delete_test_result':
        deleteTestResult();
        break;
    
    // 無效操作處理
    default:
        echo json_encode(['success' => false, 'message' => '無效的操作']);
        break;
}

/* =============================================================================
   使用者管理功能 (User Management Functions)
   ============================================================================= */

/**
 * 取得所有使用者資料
 * 功能：查詢並返回系統中所有使用者的基本資訊
 * 用途：提供給管理面板顯示使用者清單
 * 
 * 查詢欄位：
 * - user_id: 使用者識別碼
 * - user_type: 使用者類型 (technician/patient)
 * - name: 使用者姓名
 * - account: 登入帳號
 * - first_login: 首次登入標記
 * - created_at: 帳號建立時間
 * 
 * 排序方式：先按使用者類型，再按姓名排序
 * 安全性：不包含密碼等敏感資訊
 */
function getUsers() {
    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 查詢所有使用者資料
         * 排除密碼欄位，確保安全性
         * ORDER BY: 先依使用者類型分組，再依姓名排序
         */
        $stmt = $conn->prepare("SELECT user_id, user_type, name, account, first_login, created_at FROM users ORDER BY user_type, name");
        $stmt->execute();
        $users = $stmt->fetchAll();

        // 回傳成功結果和使用者資料
        echo json_encode(['success' => true, 'data' => $users]);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 捕獲查詢過程中的任何 PDO 例外
         * 回傳友善的錯誤訊息
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 新增使用者
 * 功能：在系統中建立新的使用者帳號
 * 用途：醫檢員可以新增新的受檢者或醫檢員帳號
 * 
 * 處理流程：
 * 1. 接收並清理輸入資料
 * 2. 驗證必填欄位完整性
 * 3. 驗證使用者類型有效性
 * 4. 檢查帳號是否已存在
 * 5. 新增使用者到資料庫
 * 
 * 輸入參數：
 * - user_type: 使用者類型 (technician/patient)
 * - name: 使用者姓名
 * - account: 登入帳號
 * - password: 登入密碼
 * 
 * 安全性：
 * - 輸入資料清理防止 XSS
 * - 帳號重複檢查
 * - 使用者類型白名單驗證
 */
function addUser() {
    // 接收並清理輸入資料
    $user_type = cleanInput($_POST['user_type'] ?? '');
    $name = cleanInput($_POST['name'] ?? '');
    $account = cleanInput($_POST['account'] ?? '');
    $password = $_POST['password'] ?? '';

    // 驗證必填欄位
    if (empty($user_type) || empty($name) || empty($account) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '所有欄位都必須填寫']);
        return;
    }

    // 驗證使用者類型有效性（白名單驗證）
    if (!in_array($user_type, ['technician', 'patient'])) {
        echo json_encode(['success' => false, 'message' => '無效的使用者類型']);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        /*
         * 檢查帳號是否已存在
         * 防止重複帳號造成的衝突
         * 使用 COUNT(*) 統計相同帳號的數量
         */
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE account = ?");
        $stmt->execute([$account]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => '帳號已存在']);
            return;
        }

        /*
         * 密碼雜湊處理
         * 使用 hashPassword() 函數進行密碼加密
         * 提升密碼儲存安全性
         */
        $hashed_password = hashPassword($password);
        
        /*
         * 新增使用者到資料庫
         * first_login 設為 TRUE：新使用者需要強制變更密碼
         * 使用預處理語句防止 SQL 注入
         */
        $stmt = $conn->prepare("INSERT INTO users (user_type, name, account, password, first_login) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->execute([$user_type, $name, $account, $hashed_password]);

        // 回傳成功訊息
        echo json_encode(['success' => true, 'message' => '使用者新增成功']);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 捕獲新增過程中的任何 PDO 例外
         * 回傳友善的錯誤訊息
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 更新使用者資料
 * 功能：修改現有使用者的基本資訊
 * 用途：醫檢員可以更新使用者的姓名、帳號、類型等資訊
 * 
 * 處理流程：
 * 1. 接收並清理輸入資料
 * 2. 驗證必填欄位完整性
 * 3. 檢查帳號是否與其他使用者衝突
 * 4. 更新使用者資料到資料庫
 * 
 * 輸入參數：
 * - user_id: 要更新的使用者識別碼
 * - user_type: 使用者類型 (technician/patient)
 * - name: 使用者姓名
 * - account: 登入帳號
 * 
 * 注意事項：
 * - 不包含密碼更新功能（密碼由使用者自行變更）
 * - 確保帳號不會與其他使用者重複
 */
function updateUser() {
    // 接收並清理輸入資料
    $user_id = $_POST['user_id'] ?? '';
    $user_type = cleanInput($_POST['user_type'] ?? '');
    $name = cleanInput($_POST['name'] ?? '');
    $account = cleanInput($_POST['account'] ?? '');

    // 驗證必填欄位
    if (empty($user_id) || empty($user_type) || empty($name) || empty($account)) {
        echo json_encode(['success' => false, 'message' => '所有欄位都必須填寫']);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 檢查帳號是否已被其他使用者使用
         * 條件：帳號相同 AND 使用者ID不同
         * 目的：允許使用者保持原帳號，但防止與他人衝突
         */
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE account = ? AND user_id != ?");
        $stmt->execute([$account, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => '帳號已被其他使用者使用']);
            return;
        }

        /*
         * 更新使用者資料
         * 更新欄位：使用者類型、姓名、帳號
         * 不更新：密碼、first_login 狀態
         */
        $stmt = $conn->prepare("UPDATE users SET user_type = ?, name = ?, account = ? WHERE user_id = ?");
        $stmt->execute([$user_type, $name, $account, $user_id]);

        // 回傳成功訊息
        echo json_encode(['success' => true, 'message' => '使用者更新成功']);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 捕獲更新過程中的任何 PDO 例外
         * 回傳友善的錯誤訊息
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 刪除使用者
 * 功能：從系統中移除指定的使用者帳號
 * 用途：醫檢員可以刪除不再需要的使用者帳號
 * 
 * 處理流程：
 * 1. 驗證使用者ID有效性
 * 2. 防止自刪除操作（安全機制）
 * 3. 從資料庫中刪除使用者記錄
 * 
 * 輸入參數：
 * - user_id: 要刪除的使用者識別碼
 * 
 * 安全機制：
 * - 防止管理員刪除自己的帳號
 * - 確保操作的不可逆性
 * - 相關資料的級聯處理（由資料庫外鍵約束處理）
 */
function deleteUser() {
    // 接收使用者ID
    $user_id = $_POST['user_id'] ?? '';

    // 驗證使用者ID不能為空
    if (empty($user_id)) {
        echo json_encode(['success' => false, 'message' => '使用者ID不能為空']);
        return;
    }

    /*
     * 安全檢查：防止自刪除
     * 比較要刪除的使用者ID與當前登入使用者ID
     * 目的：避免管理員意外刪除自己的帳號
     */
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => '不能刪除自己的帳號']);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 執行刪除操作
         * 注意：相關的檢查結果等資料會由資料庫外鍵約束處理
         * 可能需要先處理相關資料或設定為 NULL
         */
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // 回傳成功訊息
        echo json_encode(['success' => true, 'message' => '使用者刪除成功']);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 可能的錯誤原因：
         * - 外鍵約束衝突（使用者有相關檢查記錄）
         * - 使用者不存在
         * - 資料庫連線問題
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/* =============================================================================
   檢查項目管理功能 (Medical Items Management Functions)
   ============================================================================= */

/**
 * 取得所有醫檢項目
 * 功能：查詢並返回系統中所有可用的健康檢查項目
 * 用途：提供給管理面板顯示檢查項目清單，供新增檢查結果時選擇
 * 
 * 查詢欄位：
 * - item_id: 檢查項目識別碼
 * - item_name: 檢查項目名稱
 * - description: 檢查項目說明
 * - created_at: 項目建立時間
 * 
 * 排序方式：按檢查項目名稱排序，便於查找
 */
function getMedicalItems() {
    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 查詢所有檢查項目
         * SELECT *: 取得所有欄位資訊
         * ORDER BY: 按項目名稱排序，提升使用者體驗
         */
        $stmt = $conn->prepare("SELECT * FROM medical_items ORDER BY item_name");
        $stmt->execute();
        $items = $stmt->fetchAll();

        // 回傳成功結果和檢查項目資料
        echo json_encode(['success' => true, 'data' => $items]);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 捕獲查詢過程中的任何 PDO 例外
         * 回傳友善的錯誤訊息
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 新增醫檢項目
 * 功能：在系統中建立新的健康檢查項目
 * 用途：醫檢員可以新增新的檢查項目供日後檢查使用
 * 
 * 處理流程：
 * 1. 接收並清理輸入資料
 * 2. 驗證項目名稱不能為空
 * 3. 新增檢查項目到資料庫
 * 
 * 輸入參數：
 * - item_name: 檢查項目名稱（必填）
 * - description: 檢查項目說明（可選）
 * 
 * 設計考量：
 * - 項目名稱為必填，說明為可選
 * - 不檢查重複項目名稱（允許類似項目存在）
 */
function addMedicalItem() {
    // 接收並清理輸入資料
    $item_name = cleanInput($_POST['item_name'] ?? '');
    $description = cleanInput($_POST['description'] ?? '');

    // 驗證項目名稱不能為空
    if (empty($item_name)) {
        echo json_encode(['success' => false, 'message' => '項目名稱不能為空']);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 新增檢查項目到資料庫
         * item_name: 檢查項目名稱
         * description: 檢查項目說明（可為空）
         */
        $stmt = $conn->prepare("INSERT INTO medical_items (item_name, description) VALUES (?, ?)");
        $stmt->execute([$item_name, $description]);

        // 回傳成功訊息
        echo json_encode(['success' => true, 'message' => '醫檢項目新增成功']);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 可能的錯誤原因：
         * - 資料庫連線問題
         * - 欄位長度超出限制
         * - 其他資料庫約束違反
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 更新醫檢項目
 * 功能：修改現有檢查項目的名稱和說明
 * 用途：醫檢員可以更新檢查項目的資訊
 * 
 * 處理流程：
 * 1. 接收並清理輸入資料
 * 2. 驗證項目ID和名稱不能為空
 * 3. 更新檢查項目資料到資料庫
 * 
 * 輸入參數：
 * - item_id: 要更新的檢查項目識別碼（必填）
 * - item_name: 檢查項目名稱（必填）
 * - description: 檢查項目說明（可選）
 * 
 * 設計考量：
 * - 項目ID和名稱為必填
 * - 說明可以更新為空值
 * - 不會影響既有的檢查結果記錄
 */
function updateMedicalItem() {
    // 接收並清理輸入資料
    $item_id = $_POST['item_id'] ?? '';
    $item_name = cleanInput($_POST['item_name'] ?? '');
    $description = cleanInput($_POST['description'] ?? '');

    // 驗證必填欄位
    if (empty($item_id) || empty($item_name)) {
        echo json_encode(['success' => false, 'message' => '項目ID和名稱不能為空']);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 更新檢查項目資料
         * 更新欄位：項目名稱、項目說明
         * 不更新：項目ID、建立時間
         */
        $stmt = $conn->prepare("UPDATE medical_items SET item_name = ?, description = ? WHERE item_id = ?");
        $stmt->execute([$item_name, $description, $item_id]);

        // 回傳成功訊息
        echo json_encode(['success' => true, 'message' => '醫檢項目更新成功']);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 可能的錯誤原因：
         * - 項目ID不存在
         * - 資料庫連線問題
         * - 欄位長度超出限制
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 刪除醫檢項目
 * 功能：從系統中移除指定的檢查項目
 * 用途：醫檢員可以刪除不再使用的檢查項目
 * 
 * 處理流程：
 * 1. 驗證項目ID有效性
 * 2. 從資料庫中刪除檢查項目記錄
 * 
 * 輸入參數：
 * - item_id: 要刪除的檢查項目識別碼
 * 
 * 注意事項：
 * - 刪除項目可能影響相關的檢查結果記錄
 * - 應考慮是否需要先檢查是否有相關檢查結果
 * - 外鍵約束可能會阻止刪除操作
 */
function deleteMedicalItem() {
    // 接收項目ID
    $item_id = $_POST['item_id'] ?? '';

    // 驗證項目ID不能為空
    if (empty($item_id)) {
        echo json_encode(['success' => false, 'message' => '項目ID不能為空']);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 執行刪除操作
         * 注意：如果該項目有相關的檢查結果，
         * 可能因外鍵約束而無法刪除
         */
        $stmt = $conn->prepare("DELETE FROM medical_items WHERE item_id = ?");
        $stmt->execute([$item_id]);

        // 回傳成功訊息
        echo json_encode(['success' => true, 'message' => '醫檢項目刪除成功']);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 可能的錯誤原因：
         * - 外鍵約束衝突（有相關檢查結果）
         * - 項目不存在
         * - 資料庫連線問題
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/* =============================================================================
   檢查結果管理功能 (Test Results Management Functions)
   ============================================================================= */

/**
 * 取得所有檢查結果
 * 功能：查詢並返回系統中所有檢查結果的完整資訊
 * 用途：提供給管理面板顯示檢查結果清單，供管理和查詢使用
 * 
 * 查詢資料包含：
 * - 檢查結果基本資訊：分數、檢查日期、備註
 * - 受檢者資訊：姓名、帳號
 * - 檢查項目資訊：項目名稱
 * - 檢測員資訊：檢測員姓名
 * 
 * JOIN 關係：
 * - test_results ⟵⟶ users (patient)：取得受檢者資訊
 * - test_results ⟵⟶ medical_items：取得檢查項目資訊
 * - test_results ⟵⟶ users (technician)：取得檢測員資訊
 * 
 * 排序方式：先按檢查日期降序，再按受檢者姓名、項目名稱排序
 */
function getTestResults() {
    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 複雜查詢：結合多個資料表取得完整檢查結果資訊
         * 
         * 資料表關聯：
         * - tr (test_results): 主要檢查結果資料
         * - u1 (users): 受檢者資料 (patient)
         * - mi (medical_items): 檢查項目資料
         * - u2 (users): 檢測員資料 (technician)
         * 
         * WHERE 條件：
         * - u1.user_type = 'patient': 確保 u1 是受檢者
         * - u2.user_type = 'technician': 確保 u2 是檢測員
         * 
         * ORDER BY：
         * - test_date DESC: 最新檢查結果在前
         * - u1.name: 相同日期按受檢者姓名排序
         * - mi.item_name: 相同受檢者按項目名稱排序
         */
        $stmt = $conn->prepare("
            SELECT tr.result_id, tr.score, tr.test_date, tr.notes,
                   u1.name AS patient_name, u1.account AS patient_account,
                   mi.item_name,
                   u2.name AS technician_name
            FROM test_results tr
            JOIN users u1 ON tr.patient_id = u1.user_id AND u1.user_type = 'patient'
            JOIN medical_items mi ON tr.item_id = mi.item_id
            JOIN users u2 ON tr.technician_id = u2.user_id AND u2.user_type = 'technician'
            ORDER BY tr.test_date DESC, u1.name, mi.item_name
        ");
        $stmt->execute();
        $results = $stmt->fetchAll();

        // 回傳成功結果和檢查結果資料
        echo json_encode(['success' => true, 'data' => $results]);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 可能的錯誤原因：
         * - JOIN 關聯失敗（資料完整性問題）
         * - 資料庫連線問題
         * - 查詢語法錯誤
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 新增檢查結果
 * 功能：為受檢者新增特定項目的檢查結果
 * 用途：醫檢員輸入檢查數據和評分
 * 
 * 處理流程：
 * 1. 接收並驗證輸入資料
 * 2. 驗證分數範圍有效性
 * 3. 檢查是否已有重複記錄
 * 4. 新增檢查結果到資料庫
 * 
 * 輸入參數：
 * - patient_id: 受檢者識別碼（必填）
 * - item_id: 檢查項目識別碼（必填）
 * - score: 檢查分數（必填，1-10分）
 * - test_date: 檢查日期（必填）
 * - notes: 檢查備註（可選）
 * 
 * 業務規則：
 * - 每個受檢者每個項目只能有一筆記錄
 * - 分數範圍：1-10分
 * - 自動記錄檢測員為當前登入使用者
 */
function addTestResult() {
    // 接收輸入資料
    $patient_id = $_POST['patient_id'] ?? '';
    $item_id = $_POST['item_id'] ?? '';
    $score = $_POST['score'] ?? '';
    $test_date = $_POST['test_date'] ?? '';
    $notes = cleanInput($_POST['notes'] ?? '');

    // 驗證必填欄位
    if (empty($patient_id) || empty($item_id) || empty($score) || empty($test_date)) {
        echo json_encode(['success' => false, 'message' => '所有必填欄位都必須填寫']);
        return;
    }

    // 驗證分數範圍（1-10分）
    if ($score < 1 || $score > 10) {
        echo json_encode(['success' => false, 'message' => '分數必須在 1-10 之間']);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 檢查重複記錄
         * 業務規則：每個受檢者每個檢查項目只能有一筆記錄
         * 如果需要更新，應使用更新功能而非新增
         */
        $stmt = $conn->prepare("SELECT COUNT(*) FROM test_results WHERE patient_id = ? AND item_id = ?");
        $stmt->execute([$patient_id, $item_id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => '該受檢者已有此項檢查記錄，請使用更新功能']);
            return;
        }

        /*
         * 新增檢查結果
         * technician_id 自動設為當前登入的醫檢員
         * 確保資料的完整性和可追蹤性
         */
        $stmt = $conn->prepare("INSERT INTO test_results (patient_id, item_id, score, test_date, technician_id, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $item_id, $score, $test_date, $_SESSION['user_id'], $notes]);

        // 回傳成功訊息
        echo json_encode(['success' => true, 'message' => '檢查結果新增成功']);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 可能的錯誤原因：
         * - 外鍵約束失敗（patient_id, item_id, technician_id 不存在）
         * - 資料格式錯誤（日期格式、分數範圍）
         * - 資料庫連線問題
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 更新檢查結果
 * 功能：修改現有檢查結果的分數、日期和備註
 * 用途：醫檢員可以更正或更新已輸入的檢查資料
 * 
 * 處理流程：
 * 1. 接收並驗證輸入資料
 * 2. 驗證分數範圍有效性
 * 3. 更新檢查結果資料到資料庫
 * 
 * 輸入參數：
 * - result_id: 檢查結果識別碼（必填）
 * - score: 檢查分數（必填，1-10分）
 * - test_date: 檢查日期（必填）
 * - notes: 檢查備註（可選）
 * 
 * 設計考量：
 * - 不允許修改受檢者和檢查項目（業務邏輯）
 * - 自動更新檢測員為當前登入使用者
 * - 分數範圍驗證確保資料有效性
 */
function updateTestResult() {
    // 接收輸入資料
    $result_id = $_POST['result_id'] ?? '';
    $score = $_POST['score'] ?? '';
    $test_date = $_POST['test_date'] ?? '';
    $notes = cleanInput($_POST['notes'] ?? '');

    // 驗證必填欄位
    if (empty($result_id) || empty($score) || empty($test_date)) {
        echo json_encode(['success' => false, 'message' => '所有必填欄位都必須填寫']);
        return;
    }

    // 驗證分數範圍（1-10分）
    if ($score < 1 || $score > 10) {
        echo json_encode(['success' => false, 'message' => '分數必須在 1-10 之間']);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 更新檢查結果
         * 更新欄位：分數、檢查日期、檢測員、備註
         * 不更新：result_id、patient_id、item_id（保持檢查記錄的身份）
         * technician_id 更新為當前登入使用者，記錄最後修改者
         */
        $stmt = $conn->prepare("UPDATE test_results SET score = ?, test_date = ?, technician_id = ?, notes = ? WHERE result_id = ?");
        $stmt->execute([$score, $test_date, $_SESSION['user_id'], $notes, $result_id]);

        // 回傳成功訊息
        echo json_encode(['success' => true, 'message' => '檢查結果更新成功']);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 可能的錯誤原因：
         * - result_id 不存在
         * - 資料格式錯誤（日期格式、分數範圍）
         * - 資料庫連線問題
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 刪除檢查結果
 * 功能：從系統中移除指定的檢查結果記錄
 * 用途：醫檢員可以刪除錯誤或不需要的檢查記錄
 * 
 * 處理流程：
 * 1. 驗證結果ID有效性
 * 2. 從資料庫中刪除檢查結果記錄
 * 
 * 輸入參數：
 * - result_id: 要刪除的檢查結果識別碼
 * 
 * 注意事項：
 * - 刪除操作不可逆，需謹慎使用
 * - 建議在前端加入確認對話框
 * - 考慮是否需要軟刪除（標記為已刪除而非實際刪除）
 */
function deleteTestResult() {
    // 接收結果ID
    $result_id = $_POST['result_id'] ?? '';

    // 驗證結果ID不能為空
    if (empty($result_id)) {
        echo json_encode(['success' => false, 'message' => '結果ID不能為空']);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 執行刪除操作
         * 直接從資料庫中移除檢查結果記錄
         * 注意：這是硬刪除，資料將無法復原
         */
        $stmt = $conn->prepare("DELETE FROM test_results WHERE result_id = ?");
        $stmt->execute([$result_id]);

        // 回傳成功訊息
        echo json_encode(['success' => true, 'message' => '檢查結果刪除成功']);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 可能的錯誤原因：
         * - result_id 不存在
         * - 資料庫連線問題
         * - 權限不足
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/* =============================================================================
   檔案結束 (End of File)
   ============================================================================= */
?>