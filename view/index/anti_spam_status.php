<?php
/**
 * 防刷状态API
 * 返回当前用户的防刷限制状态
 */
header('Content-Type: application/json; charset=utf-8');

$codeuse=0; $emailuse=0;$directoryPath = '../../';
include("../../core/xiaocore.php");
include("../../core/anti_spam.php");

$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$email = $_GET['email'] ?? '';

try {
    // 获取防刷设置
    $settings = getAntiSpamSettings($conn, 'admin');
    
    // 检查IP封禁状态
    $ban_status = isIPBanned($conn, 'admin', $ip);
    
    // 检查IP今日抽奖次数
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ip_logs WHERE ip = ? AND date = ? AND action = 'lottery'");
    $stmt->bind_param("ss", $ip, $today);
    $stmt->execute();
    $ip_today_count = $stmt->get_result()->fetch_assoc()['count'];
    
    // 检查邮箱今日抽奖次数（如果提供了邮箱）
    $email_today_count = 0;
    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM lottery_logs WHERE account = ? AND date = ?");
        $stmt->bind_param("ss", $email, $today);
        $stmt->execute();
        $email_today_count = $stmt->get_result()->fetch_assoc()['count'];
    }
    
    // 获取抽奖限制
    $stmt = $conn->prepare("SELECT daily_limit, total_limit FROM lottery_limits WHERE id = 1");
    $stmt->execute();
    $lottery_limits = $stmt->get_result()->fetch_assoc();
    if (!$lottery_limits) {
        $lottery_limits = ['daily_limit' => 2, 'total_limit' => 10];
    }
    
    // 检查最后操作时间
    $last_lottery_time = null;
    $last_email_time = null;
    
    if (!empty($email)) {
        // 检查最后抽奖时间
        $stmt = $conn->prepare("SELECT last_action FROM time_intervals WHERE identifier = ? AND action = 'lottery'");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result) {
            $last_lottery_time = strtotime($result['last_action']);
        }
        
        // 检查最后邮件时间
        $stmt = $conn->prepare("SELECT last_action FROM time_intervals WHERE identifier = ? AND action = 'email'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result) {
            $last_email_time = strtotime($result['last_action']);
        }
    }
    
    $current_time = time();
    
    // 计算剩余冷却时间
    $lottery_cooldown = 0;
    $email_cooldown = 0;
    
    if ($last_lottery_time) {
        $lottery_cooldown = max(0, $settings['time_interval'] - ($current_time - $last_lottery_time));
    }
    
    if ($last_email_time) {
        $email_cooldown = max(0, $settings['email_cooldown'] - ($current_time - $last_email_time));
    }
    
    // 构建响应
    $response = [
        'success' => true,
        'ip' => $ip,
        'settings' => [
            'ip_limit_enabled' => $settings['ip_limit_enabled'],
            'ip_daily_limit' => $settings['ip_daily_limit'],
            'time_interval' => $settings['time_interval'],
            'email_cooldown' => $settings['email_cooldown']
        ],
        'status' => [
            'ip_banned' => $ban_status['banned'],
            'ban_remaining' => $ban_status['banned'] ? $ban_status['remaining'] : 0,
            'ip_today_count' => $ip_today_count,
            'ip_remaining' => max(0, $settings['ip_daily_limit'] - $ip_today_count),
            'email_today_count' => $email_today_count,
            'email_daily_remaining' => max(0, $lottery_limits['daily_limit'] - $email_today_count),
            'lottery_cooldown' => $lottery_cooldown,
            'email_cooldown' => $email_cooldown
        ],
        'limits' => [
            'daily_limit' => $lottery_limits['daily_limit'],
            'total_limit' => $lottery_limits['total_limit']
        ],
        'can_lottery' => !$ban_status['banned'] && 
                        ($ip_today_count < $settings['ip_daily_limit'] || !$settings['ip_limit_enabled']) &&
                        $lottery_cooldown == 0,
        'can_send_email' => !$ban_status['banned'] && $email_cooldown == 0
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => '获取状态失败',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
