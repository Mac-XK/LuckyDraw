<?php
$codeuse=0; 
$emailuse=0;
$directoryPath = '../../';
include("../../core/xiaocore.php");

// 设置响应头为JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userEmail = trim($_POST['email'] ?? '');
    
    if (empty($userEmail) || !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => '请输入有效的邮箱地址'
        ]);
        exit;
    }

    // QQ邮箱格式验证
    if (!preg_match('/^[0-9]+@qq\.com$/', $userEmail)) {
        echo json_encode([
            'success' => false,
            'message' => '请输入正确的QQ邮箱格式，如：123456789@qq.com（纯数字@qq.com）'
        ]);
        exit;
    }
    
    try {
        // 查询中奖记录
        $stmt = $conn->prepare("
            SELECT logs.id, logs.account, logs.date, logs.message, prizes.name AS prize_name 
            FROM lottery_logs AS logs 
            LEFT JOIN prizes ON logs.prize_id = prizes.id 
            WHERE logs.account = ? AND logs.prize_id IS NOT NULL 
            ORDER BY logs.date DESC, logs.message DESC, logs.id DESC
        ");
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = [
                'id' => $row['id'],
                'account' => $row['account'],
                'prize_name' => $row['prize_name'],
                'message' => $row['message'],
                'date' => $row['date']
            ];
        }
        
        $stmt->close();
        
        // 查询总抽奖次数
        $total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM lottery_logs WHERE account = ?");
        $total_stmt->bind_param("s", $userEmail);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result()->fetch_assoc();
        $total_stmt->close();
        
        // 查询今日抽奖次数
        $today = date("Y-m-d");
        $today_stmt = $conn->prepare("SELECT COUNT(*) as today FROM lottery_logs WHERE account = ? AND date = ?");
        $today_stmt->bind_param("ss", $userEmail, $today);
        $today_stmt->execute();
        $today_result = $today_stmt->get_result()->fetch_assoc();
        $today_stmt->close();
        
        echo json_encode([
            'success' => true,
            'records' => $records,
            'statistics' => [
                'total_draws' => $total_result['total'],
                'today_draws' => $today_result['today'],
                'total_wins' => count($records)
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => '查询失败，请稍后重试'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => '无效的请求方法'
    ]);
}

$conn->close();
?>
