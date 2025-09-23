<?php
/**
 * ==============================================================================
 * 健康度量管理系統 - 受檢者查詢面板
 * ==============================================================================
 *
 * 檔案功能：
 * 提供受檢者查詢個人健康檢查結果和健康狀態評估的功能
 *
 * 主要功能：
 * 1. 個人檢查結果查詢：顯示所有檢查項目的結果和分數
 * 2. 健康狀態評估：根據檢查結果計算總體健康評分
 * 3. 健康建議提供：根據評分提供健康狀態描述
 * 4. 權限控制：確保只有受檢者能查看自己的資料
 *
 * API 設計：
 * - 接收 POST 請求：確保資料安全傳輸
 * - JSON 回應格式：統一的前後端資料交換格式
 * - 資料安全：只能查詢當前登入使用者的資料
 *
 * 安全特性：
 * - 身分驗證：確保只有受檢者能存取
 * - 資料隔離：使用者只能查看自己的檢查結果
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
 * 功能：確保只有已登入的受檢者能存取個人查詢面板
 * 安全性：防止未授權存取個人健康資料
 * 
 * 驗證項目：
 * 1. 使用者是否已登入 (SESSION 檢查)
 * 2. 使用者類型是否為受檢者 (patient)
 * 
 * 失敗處理：重新導向至登入頁面
 */
if (!isLoggedIn() || getUserType() !== 'patient') {
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
 * 目的：確保資料傳輸的安全性和一致性
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
 * 受檢者面板功能相對簡單，主要是查詢個人檢查結果
 * 
 * 支援的操作：
 * - get_my_results: 取得個人檢查結果和健康評估
 */

switch ($action) {
    // 個人檢查結果查詢 (Personal Test Results Query)
    case 'get_my_results':
        getMyResults();
        break;
    
    // 無效操作處理
    default:
        echo json_encode(['success' => false, 'message' => '無效的操作']);
        break;
}

/* =============================================================================
   個人檢查結果查詢功能 (Personal Test Results Query Functions)
   ============================================================================= */

/**
 * 取得個人所有檢查結果
 * 功能：查詢當前登入受檢者的所有健康檢查結果並進行健康評估
 * 用途：提供受檢者完整的個人健康資料查詢和分析
 * 
 * 功能特色：
 * 1. 資料查詢：取得個人所有檢查項目的結果
 * 2. 健康評估：計算平均健康分數
 * 3. 狀態分類：根據分數提供健康狀態描述
 * 4. 統計資訊：提供檢查項目總數等統計資料
 * 
 * 查詢資料包含：
 * - 檢查結果：分數、檢查日期、備註
 * - 檢查項目：項目名稱、項目說明
 * - 檢測員資訊：檢測員姓名
 * 
 * 健康評估標準：
 * - 8-10分：優良 (健康狀態良好)
 * - 6-7分：良好 (健康狀態可接受)
 * - 4-5分：一般 (需要注意健康)
 * - 1-3分：需要關注 (建議就醫檢查)
 * 
 * 安全性：
 * - 只查詢當前登入使用者的資料 (使用 $_SESSION['user_id'])
 * - 防止越權存取其他使用者的健康資料
 */
function getMyResults() {
    try {
        // 取得資料庫連線
        $conn = getDBConnection();
        
        /*
         * 查詢個人檢查結果
         * 
         * JOIN 關聯說明：
         * - test_results (tr): 主要檢查結果資料
         * - medical_items (mi): 檢查項目詳細資訊
         * - users (u): 檢測員資訊
         * 
         * WHERE 條件：
         * - tr.patient_id = $_SESSION['user_id']: 只查詢當前登入使用者的資料
         * - u.user_type = 'technician': 確保是檢測員資料
         * 
         * ORDER BY：
         * - tr.test_date DESC: 最新檢查結果在前
         * - mi.item_name: 相同日期按檢查項目名稱排序
         */
        $stmt = $conn->prepare("
            SELECT tr.result_id, tr.score, tr.test_date, tr.notes,
                   mi.item_name, mi.description as item_description,
                   u.name AS technician_name
            FROM test_results tr
            JOIN medical_items mi ON tr.item_id = mi.item_id
            JOIN users u ON tr.technician_id = u.user_id AND u.user_type = 'technician'
            WHERE tr.patient_id = ?
            ORDER BY tr.test_date DESC, mi.item_name
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $results = $stmt->fetchAll();

        /*
         * 健康狀態評估計算
         * 功能：根據所有檢查結果計算平均分數和健康狀態
         */
        $total_score = 0;  // 總分數累計
        $count = 0;        // 檢查項目計數
        
        // 計算所有檢查結果的總分和項目數
        foreach ($results as $result) {
            $total_score += $result['score'];
            $count++;
        }
        
        /*
         * 平均分數計算
         * 使用 round() 函數四捨五入到小數點後兩位
         * 如果沒有檢查結果，平均分數為 0
         */
        $average_score = $count > 0 ? round($total_score / $count, 2) : 0;
        
        /*
         * 健康狀態評估
         * 根據平均分數區間判定健康狀態等級
         * 評估標準基於一般健康評估指標
         */
        if ($average_score >= 8) {
            $health_status = '優良';        // 8-10分：健康狀態優秀
        } elseif ($average_score >= 6) {
            $health_status = '良好';        // 6-7分：健康狀態良好
        } elseif ($average_score >= 4) {
            $health_status = '一般';        // 4-5分：健康狀態一般
        } else {
            $health_status = '需要關注';    // 1-3分：需要醫療關注
        }

        /*
         * 回傳完整的檢查結果和統計資訊
         * 包含：
         * - success: 操作成功標記
         * - data: 詳細檢查結果陣列
         * - summary: 統計摘要資訊
         */
        echo json_encode([
            'success' => true, 
            'data' => $results,
            'summary' => [
                'total_items' => $count,           // 檢查項目總數
                'average_score' => $average_score, // 平均健康分數
                'health_status' => $health_status  // 健康狀態評估
            ]
        ]);
    } catch (PDOException $e) {
        /*
         * 資料庫錯誤處理
         * 可能的錯誤原因：
         * - JOIN 關聯失敗（資料完整性問題）
         * - 使用者ID無效
         * - 資料庫連線問題
         * - 查詢語法錯誤
         */
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/* =============================================================================
   檔案結束 (End of File)
   ============================================================================= */
?>