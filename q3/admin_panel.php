<?php
/**
 * 健康度量管理系統 - 醫檢員管理面板
 */
require_once 'config.php';

// 檢查是否為醫檢員身分
if (!isLoggedIn() || getUserType() !== 'technician') {
    redirect('team021.html');
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '僅支援 POST 請求']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
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
    default:
        echo json_encode(['success' => false, 'message' => '無效的操作']);
        break;
}

/**
 * 取得所有使用者資料
 */
function getUsers() {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT user_id, user_type, name, account, first_login, created_at FROM users ORDER BY user_type, name");
        $stmt->execute();
        $users = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $users]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 新增使用者
 */
function addUser() {
    $user_type = cleanInput($_POST['user_type'] ?? '');
    $name = cleanInput($_POST['name'] ?? '');
    $account = cleanInput($_POST['account'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($user_type) || empty($name) || empty($account) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '所有欄位都必須填寫']);
        return;
    }

    if (!in_array($user_type, ['technician', 'patient'])) {
        echo json_encode(['success' => false, 'message' => '無效的使用者類型']);
        return;
    }

    try {
        $conn = getDBConnection();
        
        // 檢查帳號是否已存在
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE account = ?");
        $stmt->execute([$account]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => '帳號已存在']);
            return;
        }

        $hashed_password = hashPassword($password);
        $stmt = $conn->prepare("INSERT INTO users (user_type, name, account, password, first_login) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->execute([$user_type, $name, $account, $hashed_password]);

        echo json_encode(['success' => true, 'message' => '使用者新增成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 更新使用者
 */
function updateUser() {
    $user_id = $_POST['user_id'] ?? '';
    $user_type = cleanInput($_POST['user_type'] ?? '');
    $name = cleanInput($_POST['name'] ?? '');
    $account = cleanInput($_POST['account'] ?? '');

    if (empty($user_id) || empty($user_type) || empty($name) || empty($account)) {
        echo json_encode(['success' => false, 'message' => '所有欄位都必須填寫']);
        return;
    }

    try {
        $conn = getDBConnection();
        
        // 檢查帳號是否已被其他使用者使用
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE account = ? AND user_id != ?");
        $stmt->execute([$account, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => '帳號已被其他使用者使用']);
            return;
        }

        $stmt = $conn->prepare("UPDATE users SET user_type = ?, name = ?, account = ? WHERE user_id = ?");
        $stmt->execute([$user_type, $name, $account, $user_id]);

        echo json_encode(['success' => true, 'message' => '使用者更新成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 刪除使用者
 */
function deleteUser() {
    $user_id = $_POST['user_id'] ?? '';

    if (empty($user_id)) {
        echo json_encode(['success' => false, 'message' => '使用者ID不能為空']);
        return;
    }

    // 不能刪除自己
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => '不能刪除自己的帳號']);
        return;
    }

    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);

        echo json_encode(['success' => true, 'message' => '使用者刪除成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 取得所有醫檢項目
 */
function getMedicalItems() {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM medical_items ORDER BY item_name");
        $stmt->execute();
        $items = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $items]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 新增醫檢項目
 */
function addMedicalItem() {
    $item_name = cleanInput($_POST['item_name'] ?? '');
    $description = cleanInput($_POST['description'] ?? '');

    if (empty($item_name)) {
        echo json_encode(['success' => false, 'message' => '項目名稱不能為空']);
        return;
    }

    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO medical_items (item_name, description) VALUES (?, ?)");
        $stmt->execute([$item_name, $description]);

        echo json_encode(['success' => true, 'message' => '醫檢項目新增成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 更新醫檢項目
 */
function updateMedicalItem() {
    $item_id = $_POST['item_id'] ?? '';
    $item_name = cleanInput($_POST['item_name'] ?? '');
    $description = cleanInput($_POST['description'] ?? '');

    if (empty($item_id) || empty($item_name)) {
        echo json_encode(['success' => false, 'message' => '項目ID和名稱不能為空']);
        return;
    }

    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE medical_items SET item_name = ?, description = ? WHERE item_id = ?");
        $stmt->execute([$item_name, $description, $item_id]);

        echo json_encode(['success' => true, 'message' => '醫檢項目更新成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 刪除醫檢項目
 */
function deleteMedicalItem() {
    $item_id = $_POST['item_id'] ?? '';

    if (empty($item_id)) {
        echo json_encode(['success' => false, 'message' => '項目ID不能為空']);
        return;
    }

    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM medical_items WHERE item_id = ?");
        $stmt->execute([$item_id]);

        echo json_encode(['success' => true, 'message' => '醫檢項目刪除成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 取得所有檢查結果
 */
function getTestResults() {
    try {
        $conn = getDBConnection();
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

        echo json_encode(['success' => true, 'data' => $results]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 新增檢查結果
 */
function addTestResult() {
    $patient_id = $_POST['patient_id'] ?? '';
    $item_id = $_POST['item_id'] ?? '';
    $score = $_POST['score'] ?? '';
    $test_date = $_POST['test_date'] ?? '';
    $notes = cleanInput($_POST['notes'] ?? '');

    if (empty($patient_id) || empty($item_id) || empty($score) || empty($test_date)) {
        echo json_encode(['success' => false, 'message' => '所有必填欄位都必須填寫']);
        return;
    }

    if ($score < 1 || $score > 10) {
        echo json_encode(['success' => false, 'message' => '分數必須在 1-10 之間']);
        return;
    }

    try {
        $conn = getDBConnection();
        
        // 檢查是否已有該病患的該項檢查記錄
        $stmt = $conn->prepare("SELECT COUNT(*) FROM test_results WHERE patient_id = ? AND item_id = ?");
        $stmt->execute([$patient_id, $item_id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => '該受檢者已有此項檢查記錄，請使用更新功能']);
            return;
        }

        $stmt = $conn->prepare("INSERT INTO test_results (patient_id, item_id, score, test_date, technician_id, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $item_id, $score, $test_date, $_SESSION['user_id'], $notes]);

        echo json_encode(['success' => true, 'message' => '檢查結果新增成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 更新檢查結果
 */
function updateTestResult() {
    $result_id = $_POST['result_id'] ?? '';
    $score = $_POST['score'] ?? '';
    $test_date = $_POST['test_date'] ?? '';
    $notes = cleanInput($_POST['notes'] ?? '');

    if (empty($result_id) || empty($score) || empty($test_date)) {
        echo json_encode(['success' => false, 'message' => '所有必填欄位都必須填寫']);
        return;
    }

    if ($score < 1 || $score > 10) {
        echo json_encode(['success' => false, 'message' => '分數必須在 1-10 之間']);
        return;
    }

    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE test_results SET score = ?, test_date = ?, technician_id = ?, notes = ? WHERE result_id = ?");
        $stmt->execute([$score, $test_date, $_SESSION['user_id'], $notes, $result_id]);

        echo json_encode(['success' => true, 'message' => '檢查結果更新成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 刪除檢查結果
 */
function deleteTestResult() {
    $result_id = $_POST['result_id'] ?? '';

    if (empty($result_id)) {
        echo json_encode(['success' => false, 'message' => '結果ID不能為空']);
        return;
    }

    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM test_results WHERE result_id = ?");
        $stmt->execute([$result_id]);

        echo json_encode(['success' => true, 'message' => '檢查結果刪除成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}
?>