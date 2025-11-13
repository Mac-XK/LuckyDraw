<?php
/**
 * 防刷机制核心函数
 * 包含IP限制、时间间隔检查、自动封禁等功能
 */

/**
 * 获取防刷设置
 */
function getAntiSpamSettings($conn, $username) {
    $stmt = $conn->prepare("SELECT update1 FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    $settings = json_decode($result['update1'] ?? '{}', true);
    if (!is_array($settings)) $settings = [];
    
    $default_settings = [
        'ip_limit_enabled' => 1,
        'ip_daily_limit' => 5,
        'time_interval' => 60,
        'email_cooldown' => 60,
        'max_attempts' => 10,
        'ban_duration' => 3600
    ];
    
    return array_merge($default_settings, $settings);
}

/**
 * 获取封禁IP列表
 */
function getBannedIPs($conn, $username) {
    $stmt = $conn->prepare("SELECT update3 FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    $banned_ips = json_decode($result['update3'] ?? '[]', true);
    if (!is_array($banned_ips)) $banned_ips = [];
    
    // 清理过期的封禁
    $current_time = time();
    $banned_ips = array_filter($banned_ips, function($ban) use ($current_time) {
        return $ban['banned_until'] > $current_time;
    });
    
    return $banned_ips;
}

/**
 * 检查IP是否被封禁
 */
function isIPBanned($conn, $username, $ip) {
    $banned_ips = getBannedIPs($conn, $username);
    
    foreach ($banned_ips as $ban) {
        if ($ban['ip'] === $ip) {
            return [
                'banned' => true,
                'until' => $ban['banned_until'],
                'remaining' => $ban['banned_until'] - time()
            ];
        }
    }
    
    return ['banned' => false];
}

/**
 * 封禁IP
 */
function banIP($conn, $username, $ip, $duration) {
    $banned_ips = getBannedIPs($conn, $username);
    
    $current_time = time();
    $banned_until = $current_time + $duration;
    
    // 添加新的封禁记录
    $banned_ips[] = [
        'ip' => $ip,
        'banned_at' => $current_time,
        'banned_until' => $banned_until
    ];
    
    // 更新数据库
    $stmt = $conn->prepare("UPDATE admins SET update3 = ? WHERE username = ?");
    $banned_ips_json = json_encode($banned_ips);
    $stmt->bind_param("ss", $banned_ips_json, $username);
    $stmt->execute();
    
    return $banned_until;
}

/**
 * 检查IP每日抽奖次数
 */
function checkIPDailyLimit($conn, $ip, $daily_limit) {
    $today = date('Y-m-d');
    
    // 创建IP记录表（如果不存在）
    $conn->query("CREATE TABLE IF NOT EXISTS ip_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) NOT NULL,
        action VARCHAR(20) NOT NULL,
        timestamp DATETIME NOT NULL,
        date DATE NOT NULL,
        INDEX idx_ip_date (ip, date),
        INDEX idx_ip_timestamp (ip, timestamp)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 检查今日该IP的抽奖次数
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ip_logs WHERE ip = ? AND date = ? AND action = 'lottery'");
    $stmt->bind_param("ss", $ip, $today);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['count'] < $daily_limit;
}

/**
 * 记录IP操作
 */
function logIPAction($conn, $ip, $action) {
    $conn->query("CREATE TABLE IF NOT EXISTS ip_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) NOT NULL,
        action VARCHAR(20) NOT NULL,
        timestamp DATETIME NOT NULL,
        date DATE NOT NULL,
        INDEX idx_ip_date (ip, date),
        INDEX idx_ip_timestamp (ip, timestamp)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $stmt = $conn->prepare("INSERT INTO ip_logs (ip, action, timestamp, date) VALUES (?, ?, NOW(), CURDATE())");
    $stmt->bind_param("ss", $ip, $action);
    $stmt->execute();
}

/**
 * 检查时间间隔
 */
function checkTimeInterval($conn, $identifier, $action, $interval) {
    // 创建时间间隔检查表
    $conn->query("CREATE TABLE IF NOT EXISTS time_intervals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        identifier VARCHAR(100) NOT NULL,
        action VARCHAR(20) NOT NULL,
        last_action DATETIME NOT NULL,
        UNIQUE KEY unique_identifier_action (identifier, action)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $current_time = time();
    
    // 检查上次操作时间
    $stmt = $conn->prepare("SELECT UNIX_TIMESTAMP(last_action) as last_time FROM time_intervals WHERE identifier = ? AND action = ?");
    $stmt->bind_param("ss", $identifier, $action);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result) {
        $time_diff = $current_time - $result['last_time'];
        if ($time_diff < $interval) {
            return [
                'allowed' => false,
                'remaining' => $interval - $time_diff
            ];
        }
    }
    
    // 更新时间记录
    $stmt = $conn->prepare("INSERT INTO time_intervals (identifier, action, last_action) VALUES (?, ?, NOW()) 
                           ON DUPLICATE KEY UPDATE last_action = NOW()");
    $stmt->bind_param("ss", $identifier, $action);
    $stmt->execute();
    
    return ['allowed' => true];
}

/**
 * 检查是否需要自动封禁
 */
function checkAutoBan($conn, $username, $ip, $max_attempts, $ban_duration) {
    // 检查最近5分钟内的尝试次数
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ip_logs WHERE ip = ? AND timestamp > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] >= $max_attempts) {
        banIP($conn, $username, $ip, $ban_duration);
        return true;
    }
    
    return false;
}

/**
 * 综合防刷检查
 */
function antiSpamCheck($conn, $username, $email, $action = 'lottery') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $settings = getAntiSpamSettings($conn, $username);
    
    // 1. 检查IP是否被封禁
    $ban_status = isIPBanned($conn, $username, $ip);
    if ($ban_status['banned']) {
        $remaining_minutes = ceil($ban_status['remaining'] / 60);
        return [
            'allowed' => false,
            'reason' => 'ip_banned',
            'message' => "您的IP已被临时封禁，请 {$remaining_minutes} 分钟后再试"
        ];
    }
    
    // 2. 检查IP每日限制（如果启用）
    if ($settings['ip_limit_enabled'] && $action === 'lottery') {
        if (!checkIPDailyLimit($conn, $ip, $settings['ip_daily_limit'])) {
            return [
                'allowed' => false,
                'reason' => 'ip_daily_limit',
                'message' => "您的IP今日抽奖次数已达上限（{$settings['ip_daily_limit']}次）"
            ];
        }
    }
    
    // 3. 检查时间间隔
    $interval = ($action === 'email') ? $settings['email_cooldown'] : $settings['time_interval'];
    $identifier = ($action === 'email') ? $email : $ip;
    
    $time_check = checkTimeInterval($conn, $identifier, $action, $interval);
    if (!$time_check['allowed']) {
        $action_name = ($action === 'email') ? '发送邮件' : '抽奖';
        return [
            'allowed' => false,
            'reason' => 'time_interval',
            'message' => "操作过于频繁，请 {$time_check['remaining']} 秒后再{$action_name}"
        ];
    }
    
    // 4. 记录操作并检查是否需要自动封禁
    logIPAction($conn, $ip, $action);
    if (checkAutoban($conn, $username, $ip, $settings['max_attempts'], $settings['ban_duration'])) {
        return [
            'allowed' => false,
            'reason' => 'auto_banned',
            'message' => '检测到异常行为，您的IP已被临时封禁'
        ];
    }
    
    return ['allowed' => true];
}

/**
 * 获取用户友好的错误消息
 */
function getAntiSpamMessage($check_result) {
    if ($check_result['allowed']) {
        return null;
    }
    
    $messages = [
        'ip_banned' => '🚫 IP已被封禁',
        'ip_daily_limit' => '📊 IP每日限制',
        'time_interval' => '⏰ 操作过于频繁',
        'auto_banned' => '🛡️ 自动防护'
    ];
    
    $title = $messages[$check_result['reason']] ?? '❌ 操作被拒绝';
    
    return [
        'title' => $title,
        'message' => $check_result['message']
    ];
}
?>
