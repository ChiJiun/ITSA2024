<?php
/**
 * 健康度量管理系統 - 受檢者面板
 */
require_once 'config.php';

// 檢查是否為受檢者身分
if (!isLoggedIn() || getUserType() !== 'patient') {
    redirect('team021.html');
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '僅支援 POST 請求']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_my_results':
        getMyResults();
        break;
    default:
        echo json_encode(['success' => false, 'message' => '無效的操作']);
        break;
}

/**
 * 取得個人所有檢查結果
 */
function getMyResults() {
    try {
        $conn = getDBConnection();
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

        // 計算總體健康評分
        $total_score = 0;
        $count = 0;
        foreach ($results as $result) {
            $total_score += $result['score'];
            $count++;
        }
        
        $average_score = $count > 0 ? round($total_score / $count, 2) : 0;
        
        // 健康狀態評估
        $health_status = '';
        if ($average_score >= 8) {
            $health_status = '優良';
        } elseif ($average_score >= 6) {
            $health_status = '良好';
        } elseif ($average_score >= 4) {
            $health_status = '一般';
        } else {
            $health_status = '需要關注';
        }

        echo json_encode([
            'success' => true, 
            'data' => $results,
            'summary' => [
                'total_items' => $count,
                'average_score' => $average_score,
                'health_status' => $health_status
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
    }
}
?>