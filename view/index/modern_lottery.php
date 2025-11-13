<?php 
$codeuse=1; 
$emailuse=1; 
$directoryPath = '../../';
include("../../core/xiaocore.php"); 

// 设置响应头为JSON
header('Content-Type: application/json; charset=utf-8');

// 获取抽奖限制
function getLotteryLimits($conn) {
    $stmt = $conn->prepare("SELECT daily_limit, total_limit, draw_count FROM lottery_limits WHERE id = ?");
    $id = 1;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return ['daily_limit' => 2, 'total_limit' => 999, 'draw_count' => 0];
}

// 检查抽奖限制
function checkLimits($conn, $account, $date, $daily_limit, $total_limit) {
    // 检查今日抽奖次数
    $daily_stmt = $conn->prepare("SELECT COUNT(*) as count FROM lottery_logs WHERE account = ? AND date = ?");
    $daily_stmt->bind_param("ss", $account, $date);
    $daily_stmt->execute();
    $daily_result = $daily_stmt->get_result()->fetch_assoc();
    $daily_stmt->close();
    
    if ($daily_result['count'] >= $daily_limit) {
        return 1; // 今日次数已满
    }
    
    // 检查总抽奖次数
    $total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM lottery_logs WHERE account = ?");
    $total_stmt->bind_param("s", $account);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result()->fetch_assoc();
    $total_stmt->close();
    
    if ($total_result['count'] >= $total_limit) {
        return 2; // 总次数已满
    }
    
    return 0; // 可以抽奖
}

// 抽奖逻辑
function drawLottery($conn) {
    $stmt = $conn->prepare("SELECT * FROM prizes WHERE remaining > 0 ORDER BY probability DESC");
    $stmt->execute();
    $prizes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    if (empty($prizes)) {
        return null; // 没有奖品了
    }
    
    $random = mt_rand(1, 10000) / 10000; // 0-1之间的随机数
    $cumulative = 0;
    
    foreach ($prizes as $prize) {
        $cumulative += $prize['probability'];
        if ($random <= $cumulative) {
            // 减少奖品数量
            $update_stmt = $conn->prepare("UPDATE prizes SET remaining = remaining - 1 WHERE id = ?");
            $update_stmt->bind_param("i", $prize['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            return $prize;
        }
    }
    
    return null; // 没中奖
}

// 记录抽奖日志
function logLottery($conn, $account, $message, $date, $prize_id = null) {
    $stmt = $conn->prepare("INSERT INTO lottery_logs (account, message, date, prize_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $account, $message, $date, $prize_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// 主逻辑处理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $account = trim($_POST['email'] ?? '');
        $message = trim($_POST['text'] ?? '');
        $date = date("Y-m-d");
        
        // 验证邮箱
        if (empty($account) || !filter_var($account, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => '请输入有效的邮箱地址']);
            exit;
        }

        // QQ邮箱格式验证
        if (!preg_match('/^[0-9]+@qq\.com$/', $account)) {
            echo json_encode(['success' => false, 'message' => '请输入正确的QQ邮箱格式，如：123456789@qq.com（纯数字@qq.com）']);
            exit;
        }
        
        // 检查邮箱后缀
        $allowedDomains = explode(',', $info['allowemail'] ?? ''); 
        $emailDomain = substr(strrchr($account, "@"), 1); 
        
        if (!in_array('*', $allowedDomains) && !in_array($emailDomain, $allowedDomains)) {
            echo json_encode([
                'success' => false, 
                'message' => '仅支持以下邮箱后缀：' . implode(', ', $allowedDomains)
            ]);
            exit;
        }
        
        // 验证卡密（如果开启）
        if (!empty($info['update1']) && $info['update1'] == 1) {
            $kami = trim($_POST['kami'] ?? '');
            if (empty($kami)) {
                echo json_encode(['success' => false, 'message' => '请输入兑换码/卡密']);
                exit;
            }
            
            // 这里应该验证卡密的有效性
            // 简化处理，实际应该检查数据库中的卡密
        }
        
        // 验证邮箱验证码（如果开启）
        if (!empty($info['emailsend']) && $info['emailsend'] == 1) {
            $verificationCode = trim($_POST['verification-code'] ?? '');
            if (empty($verificationCode)) {
                echo json_encode(['success' => false, 'message' => '请输入邮箱验证码']);
                exit;
            }
            
            if (!isset($_SESSION['emailcode']) || $_SESSION['emailcode'] != $verificationCode) {
                echo json_encode(['success' => false, 'message' => '验证码错误或已过期']);
                exit;
            }
            unset($_SESSION['emailcode']);
        }
        
        // 获取抽奖限制并检查
        $limit = getLotteryLimits($conn);
        $limit_status = checkLimits($conn, $account, $date, $limit['daily_limit'], $limit['total_limit']);
        
        if ($limit_status === 1) {
            echo json_encode(['success' => false, 'message' => '今天的抽奖次数已达上限，明天再来吧~']);
            exit;
        } elseif ($limit_status === 2) {
            echo json_encode(['success' => false, 'message' => '本次活动的抽奖次数已用完，感谢支持~']);
            exit;
        }
        
        // 开始抽奖
        $prize = drawLottery($conn);
        
        if ($prize) {
            // 中奖了
            logLottery($conn, $account, $message, $date, $prize['id']);
            echo json_encode([
                'success' => true,
                'won' => true,
                'prize' => $prize['name'],
                'message' => "🎉 恭喜您中奖了！\n\n奖品：{$prize['name']}\n\n请保存好这个页面，我们会根据您留下的联系方式与您联系！"
            ]);
        } else {
            // 没中奖
            logLottery($conn, $account, $message, $date);
            echo json_encode([
                'success' => true,
                'won' => false,
                'message' => "😊 谢谢参与！\n\n很遗憾这次没有中奖，不要灰心，下次再来试试吧！\n\n每天都有新的机会哦~"
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '系统错误，请稍后重试']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '无效的请求方法']);
}

$conn->close();
?>
