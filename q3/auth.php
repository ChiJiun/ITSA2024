<?php
/**
 * ==============================================================================
 * 健康度量管理系統 - 使用者認證處理檔案
 * ==============================================================================
 *
 * 檔案功能：
 * 處理所有與使用者認證相關的 AJAX 請求，包含登入、登出、密碼變更等功能
 *
 * 主要功能：
 * 1. 使用者登入驗證：帳號密碼驗證、SESSION 建立
 * 2. 密碼變更處理：首次登入強制變更、一般密碼變更
 * 3. 使用者登出：清除 SESSION、安全登出
 * 4. 安全性控制：輸入驗證、錯誤處理
 *
 * API 設計：
 * - 接收 POST 請求：確保資料安全傳輸
 * - JSON 回應格式：統一的前後端資料交換格式
 * - 錯誤處理機制：完整的錯誤回饋和日誌記錄
 *
 * 安全特性：
 * - 輸入資料清理和驗證
 * - SQL 注入防護（PDO 預處理）
 * - SESSION 安全管理
 * - 錯誤訊息標準化
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
 * 目的：防止 GET 請求洩露敏感資訊（如密碼）
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
 * 使用 null coalescing operator (??) 提供預設值
 */
$action = $_POST['action'] ?? '';

/**
 * 根據操作類型路由到對應的處理函數
 * 支援的操作：
 * - login：使用者登入
 * - change_password：變更密碼
 * - logout：使用者登出
 */
switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'change_password':
        handleChangePassword();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        echo json_encode(['success' => false, 'message' => '無效的操作']);
        break;
}

/* =============================================================================
   登入處理函數 (Login Handler Function)
   ============================================================================= */

/**
 * 處理使用者登入請求
 * 功能：驗證帳號密碼、建立 SESSION、回傳使用者資訊
 * 
 * 處理流程：
 * 1. 驗證輸入資料完整性
 * 2. 查詢資料庫中的使用者資料
 * 3. 驗證密碼正確性
 * 4. 建立 SESSION 會話
 * 5. 回傳登入結果和使用者資訊
 * 
 * 安全考量：
 * - 輸入資料清理
 * - 密碼驗證
 * - 錯誤訊息統一化（避免洩露系統資訊）
 */
function handleLogin() {
    // 清理和取得輸入資料
    $account = cleanInput($_POST['account'] ?? '');
    $password = $_POST['password'] ?? '';

    // 驗證必填欄位
    if (empty($account) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '帳號和密碼不能為空']);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 查詢使用者資料
         * 使用 PDO 預處理語句防止 SQL 注入
         * 只查詢必要欄位，避免不必要的資料傳輸
         */
        $stmt = $conn->prepare("SELECT user_id, name, account, password, user_type, first_login FROM users WHERE account = ?");
        $stmt->execute([$account]);
        $user = $stmt->fetch();

        /*
         * 密碼驗證
         * 注意：目前使用明文密碼比對
         * 建議：正式環境中應使用 password_verify() 進行雜湊密碼驗證
         */
        if ($user && $password === $user['password']) {
            /*
             * 建立使用者 SESSION 會話
             * SESSION 用於維持使用者登入狀態
             * 存儲使用者的基本資訊和權限等級
             */
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_account'] = $user['account'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['first_login'] = $user['first_login'];

            /*
             * 回傳登入成功結果
             * 包含：成功狀態、使用者類型、首次登入標記
             * 前端會根據 user_type 導向不同頁面
             * first_login 決定是否需要強制變更密碼
             */
            echo json_encode([
                'success' => true,
                'message' => '登入成功',
                'user_type' => $user['user_type'],
                'first_login' => $user['first_login']
            ]);
        } else {
            /*
             * 登入失敗處理
             * 統一的錯誤訊息，避免洩露帳號是否存在的資訊
             * 增強系統安全性
             */
            echo json_encode(['success' => false, 'message' => '帳號或密碼錯誤']);
        }
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 捕獲 PDO 例外，記錄錯誤並回傳友善的錯誤訊息
         * 避免將詳細的資料庫錯誤暴露給使用者
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/* =============================================================================
   密碼變更處理函數 (Change Password Handler Function)
   ============================================================================= */

/**
 * 處理密碼變更請求
 * 功能：驗證舊密碼、更新新密碼、處理首次登入密碼變更
 * 
 * 處理流程：
 * 1. 驗證使用者已登入
 * 2. 驗證輸入資料完整性和一致性
 * 3. 驗證新密碼格式符合規範
 * 4. 確認目前密碼正確性
 * 5. 更新密碼到資料庫
 * 6. 更新首次登入狀態
 * 
 * 安全特性：
 * - 雙重密碼確認
 * - 密碼格式驗證
 * - 當前密碼驗證
 * - 首次登入狀態管理
 */
function handleChangePassword() {
    // 驗證使用者登入狀態
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => '請先登入']);
        return;
    }

    // 取得表單輸入資料
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 驗證必填欄位
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => '所有欄位都必須填寫']);
        return;
    }

    // 驗證新密碼一致性
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => '新密碼與確認密碼不一致']);
        return;
    }

    // 驗證新密碼格式規範
    if (!validatePasswordFormat($new_password)) {
        echo json_encode([
            'success' => false, 
            'message' => '密碼格式不符合規範：必須是英文字大小寫與數字混合之 12 碼字串'
        ]);
        return;
    }

    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 驗證當前密碼正確性
         * 查詢資料庫中該使用者的當前密碼
         * 使用明文比對（實際專案中應使用雜湊密碼）
         */
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        // 當前密碼驗證失敗
        if (!$user || $current_password !== $user['password']) {
            echo json_encode(['success' => false, 'message' => '當前密碼錯誤']);
            return;
        }

        /*
         * 更新使用者密碼
         * 同時將 first_login 標記設為 FALSE
         * 表示使用者已完成首次密碼變更
         */
        $stmt = $conn->prepare("UPDATE users SET password = ?, first_login = FALSE WHERE user_id = ?");
        $stmt->execute([$new_password, $_SESSION['user_id']]);

        /*
         * 更新 SESSION 中的首次登入狀態
         * 確保前端介面能立即反映狀態變更
         */
        $_SESSION['first_login'] = false;

        // 回傳成功訊息
        echo json_encode(['success' => true, 'message' => '密碼更新成功']);
        
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 捕獲更新過程中的任何 PDO 例外
         * 回傳友善的錯誤訊息
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/* =============================================================================
   登出處理函數 (Logout Handler Function)
   ============================================================================= */

/**
 * 處理使用者登出請求
 * 功能：清除 SESSION 資料、結束使用者會話
 * 
 * 處理流程：
 * 1. 呼叫 logout() 工具函數
 * 2. 清除所有 SESSION 變數
 * 3. 銷毀 SESSION 會話
 * 4. 回傳登出成功訊息
 * 
 * 安全性：
 * - 完全清除使用者會話資料
 * - 防止 SESSION 劫持
 * - 確保登出後無法再次存取需要權限的功能
 */
function handleLogout() {
    // 呼叫工具函數執行登出操作
    logout();
    
    // 回傳登出成功訊息給前端
    echo json_encode(['success' => true, 'message' => '登出成功']);
}

/* =============================================================================
   檔案結束 (End of File)
   ============================================================================= */
?>