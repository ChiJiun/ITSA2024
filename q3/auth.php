<?php
/**
 * 健康度量管理系統 - 認證處理檔案
 */
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '僅支援 POST 請求']);
    exit();
}

$action = $_POST['action'] ?? '';

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

/**
 * 處理登入
 */
function handleLogin() {
    $account = cleanInput($_POST['account'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($account) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '帳號和密碼不能為空']);
        return;
    }

    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT user_id, name, account, password, user_type, first_login FROM users WHERE account = ?");
        $stmt->execute([$account]);
        $user = $stmt->fetch();

        // 使用明文密碼比對
        if ($user && $password === $user['password']) {
            // 登入成功
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_account'] = $user['account'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['first_login'] = $user['first_login'];

            echo json_encode([
                'success' => true,
                'message' => '登入成功',
                'user_type' => $user['user_type'],
                'first_login' => $user['first_login']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => '帳號或密碼錯誤']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 處理密碼變更
 */
function handleChangePassword() {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => '請先登入']);
        return;
    }

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 驗證輸入
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => '所有欄位都必須填寫']);
        return;
    }

    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => '新密碼與確認密碼不一致']);
        return;
    }

    // 驗證新密碼格式
    if (!validatePasswordFormat($new_password)) {
        echo json_encode([
            'success' => false, 
            'message' => '密碼格式不符合規範：必須是英文字大小寫與數字混合之 12 碼字串'
        ]);
        return;
    }

    try {
        $conn = getDBConnection();
        
        // 驗證當前密碼（明文比對）
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user || $current_password !== $user['password']) {
            echo json_encode(['success' => false, 'message' => '當前密碼錯誤']);
            return;
        }

        // 更新密碼（儲存明文）
        $stmt = $conn->prepare("UPDATE users SET password = ?, first_login = FALSE WHERE user_id = ?");
        $stmt->execute([$new_password, $_SESSION['user_id']]);

        $_SESSION['first_login'] = false;

        echo json_encode(['success' => true, 'message' => '密碼更新成功']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}

/**
 * 處理登出
 */
function handleLogout() {
    logout();
    echo json_encode(['success' => true, 'message' => '登出成功']);
}
?>