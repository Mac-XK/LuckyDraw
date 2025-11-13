<?php
$codeuse=0; $emailuse=0;$directoryPath = '../../';
include("../../core/xiaocore.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: xiao_login.php');
    exit;
}

$message = null;

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $ip_limit_enabled = isset($_POST['ip_limit_enabled']) ? 1 : 0;
        $ip_daily_limit = intval($_POST['ip_daily_limit']);
        $time_interval = intval($_POST['time_interval']);
        $email_cooldown = intval($_POST['email_cooldown']);
        $max_attempts = intval($_POST['max_attempts']);
        $ban_duration = intval($_POST['ban_duration']);
        
        // 更新防刷设置
        $stmt = $conn->prepare("UPDATE admins SET 
            update1 = ?, 
            update3 = ? 
            WHERE username = ?");
        
        $settings = json_encode([
            'ip_limit_enabled' => $ip_limit_enabled,
            'ip_daily_limit' => $ip_daily_limit,
            'time_interval' => $time_interval,
            'email_cooldown' => $email_cooldown,
            'max_attempts' => $max_attempts,
            'ban_duration' => $ban_duration
        ]);
        
        $banned_ips = json_encode([]);
        
        $stmt->bind_param("sss", $settings, $banned_ips, $_SESSION['admin_username']);
        
        if ($stmt->execute()) {
            $message = "✅ 防刷设置更新成功！";
        } else {
            $message = "❌ 更新失败：" . $conn->error;
        }
    }
    
    if (isset($_POST['unban_ip'])) {
        $ip_to_unban = $_POST['ip_to_unban'];
        
        // 获取当前封禁列表
        $stmt = $conn->prepare("SELECT update3 FROM admins WHERE username = ?");
        $stmt->bind_param("s", $_SESSION['admin_username']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $banned_ips = json_decode($result['update3'] ?? '[]', true);
        if (!is_array($banned_ips)) $banned_ips = [];
        
        // 移除指定IP
        $banned_ips = array_filter($banned_ips, function($ban) use ($ip_to_unban) {
            return $ban['ip'] !== $ip_to_unban;
        });
        
        // 更新数据库
        $stmt = $conn->prepare("UPDATE admins SET update3 = ? WHERE username = ?");
        $banned_ips_json = json_encode(array_values($banned_ips));
        $stmt->bind_param("ss", $banned_ips_json, $_SESSION['admin_username']);
        
        if ($stmt->execute()) {
            $message = "✅ IP {$ip_to_unban} 已解封！";
        } else {
            $message = "❌ 解封失败：" . $conn->error;
        }
    }
    
    if (isset($_POST['clear_logs'])) {
        // 清空抽奖记录和重置计数器
        $conn->query("DELETE FROM lottery_logs");
        $conn->query("UPDATE lottery_limits SET draw_count = 0");
        $message = "✅ 抽奖记录已清空，计数器已重置！";
    }
}

// 获取当前设置
$stmt = $conn->prepare("SELECT update1, update3 FROM admins WHERE username = ?");
$stmt->bind_param("s", $_SESSION['admin_username']);
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

$settings = array_merge($default_settings, $settings);

$banned_ips = json_decode($result['update3'] ?? '[]', true);
if (!is_array($banned_ips)) $banned_ips = [];

// 获取统计信息
$today = date('Y-m-d');
$stats_query = $conn->query("
    SELECT 
        COUNT(*) as total_draws,
        COUNT(DISTINCT account) as unique_users,
        COUNT(CASE WHEN prize_id IS NOT NULL THEN 1 END) as winners,
        COUNT(CASE WHEN date = '$today' THEN 1 END) as today_draws
    FROM lottery_logs
");
$stats = $stats_query->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>防刷设置</title>
    <link rel="icon" href="favicon.ico" type="image/ico">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/materialdesignicons.min.css" rel="stylesheet">
    <link href="../../css/style.min.css" rel="stylesheet">
    <style>
        .alert-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .alert-danger { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; margin-bottom: 10px; }
        .stats-number { font-size: 1.8rem; font-weight: bold; }
        @media (max-width: 768px) {
            .stats-number { font-size: 1.4rem; }
            .stats-card { padding: 10px !important; }
        }
        .banned-ip { background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 10px; border-left: 4px solid #dc3545; }
        .setting-group { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .help-text { font-size: 0.9em; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
<div class="container-fluid p-t-15">
    <div class="row">
        <div class="col-xs-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="mdi mdi-shield-check"></i> 防刷设置</h4>
                    <small class="text-muted">配置抽奖系统的防刷机制，防止恶意用户刷抽奖</small>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- 统计信息 -->
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-3 col-xs-3">
                            <div class="stats-card text-center" style="padding: 15px;">
                                <div class="stats-number"><?php echo $stats['total_draws']; ?></div>
                                <div style="font-size: 12px;">总抽奖次数</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-3">
                            <div class="stats-card text-center" style="padding: 15px;">
                                <div class="stats-number"><?php echo $stats['unique_users']; ?></div>
                                <div style="font-size: 12px;">参与用户数</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-3">
                            <div class="stats-card text-center" style="padding: 15px;">
                                <div class="stats-number"><?php echo $stats['winners']; ?></div>
                                <div style="font-size: 12px;">中奖次数</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-3">
                            <div class="stats-card text-center" style="padding: 15px;">
                                <div class="stats-number"><?php echo $stats['today_draws']; ?></div>
                                <div style="font-size: 12px;">今日抽奖</div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <!-- IP限制设置 -->
                        <div class="setting-group">
                            <h5><i class="mdi mdi-ip-network"></i> IP限制设置</h5>
                            <div class="form-group">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="ip_limit_enabled" value="1" <?php echo $settings['ip_limit_enabled'] ? 'checked' : ''; ?>>
                                    启用IP限制
                                </label>
                                <div class="help-text">限制单个IP地址的抽奖次数</div>
                            </div>
                            <div class="form-group">
                                <label>单个IP每日抽奖限制</label>
                                <input type="number" name="ip_daily_limit" class="form-control" value="<?php echo $settings['ip_daily_limit']; ?>" min="1" max="100">
                                <div class="help-text">单个IP地址每天最多可以抽奖的次数</div>
                            </div>
                        </div>
                        
                        <!-- 时间间隔设置 -->
                        <div class="setting-group">
                            <h5><i class="mdi mdi-timer"></i> 时间间隔设置</h5>
                            <div class="form-group">
                                <label>抽奖时间间隔（秒）</label>
                                <input type="number" name="time_interval" class="form-control" value="<?php echo $settings['time_interval']; ?>" min="10" max="3600">
                                <div class="help-text">用户两次抽奖之间必须间隔的时间</div>
                            </div>
                            <div class="form-group">
                                <label>邮件发送冷却时间（秒）</label>
                                <input type="number" name="email_cooldown" class="form-control" value="<?php echo $settings['email_cooldown']; ?>" min="30" max="300">
                                <div class="help-text">用户两次发送验证码邮件之间的间隔时间</div>
                            </div>
                        </div>
                        
                        <!-- 自动封禁设置 -->
                        <div class="setting-group">
                            <h5><i class="mdi mdi-block-helper"></i> 自动封禁设置</h5>
                            <div class="form-group">
                                <label>最大尝试次数</label>
                                <input type="number" name="max_attempts" class="form-control" value="<?php echo $settings['max_attempts']; ?>" min="5" max="50">
                                <div class="help-text">IP在短时间内超过此次数将被自动封禁</div>
                            </div>
                            <div class="form-group">
                                <label>封禁时长（秒）</label>
                                <input type="number" name="ban_duration" class="form-control" value="<?php echo $settings['ban_duration']; ?>" min="300" max="86400">
                                <div class="help-text">自动封禁的持续时间（3600秒=1小时）</div>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_settings" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> 保存设置
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 封禁IP管理 -->
    <?php if (!empty($banned_ips)): ?>
    <div class="row">
        <div class="col-xs-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="mdi mdi-block-helper"></i> 封禁IP管理</h4>
                </div>
                <div class="card-body">
                    <?php foreach ($banned_ips as $ban): ?>
                        <div class="banned-ip">
                            <div class="row">
                                <div class="col-md-8">
                                    <strong>IP: <?php echo htmlspecialchars($ban['ip']); ?></strong><br>
                                    <small>封禁时间: <?php echo date('Y-m-d H:i:s', $ban['banned_at']); ?></small><br>
                                    <small>解封时间: <?php echo date('Y-m-d H:i:s', $ban['banned_until']); ?></small>
                                </div>
                                <div class="col-md-4 text-right">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="ip_to_unban" value="<?php echo htmlspecialchars($ban['ip']); ?>">
                                        <button type="submit" name="unban_ip" class="btn btn-warning btn-sm" 
                                                onclick="return confirm('确定要解封IP <?php echo htmlspecialchars($ban['ip']); ?> 吗？')">
                                            <i class="mdi mdi-lock-open"></i> 解封
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- 危险操作 -->
    <div class="row">
        <div class="col-xs-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="mdi mdi-alert"></i> 危险操作</h4>
                </div>
                <div class="card-body">
                    <form method="POST" onsubmit="return confirm('⚠️ 警告：此操作将清空所有抽奖记录并重置计数器，无法恢复！确定要继续吗？');">
                        <button type="submit" name="clear_logs" class="btn btn-danger">
                            <i class="mdi mdi-delete"></i> 清空所有抽奖记录
                        </button>
                        <small class="help-text">清空所有抽奖记录并重置抽奖次数计数器</small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../js/jquery.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>
<script src="../../js/main.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
