<?php
$codeuse=0; $emailuse=0;$directoryPath = './';
include("core/xiaocore.php");

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>防刷系统数据库更新</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .update-container { max-width: 800px; margin: 0 auto; background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .status-item { padding: 10px; margin: 5px 0; border-radius: 5px; border-left: 4px solid #28a745; background: #f8f9fa; }
        .status-error { border-left-color: #dc3545; background: #f8d7da; }
        .status-warning { border-left-color: #ffc107; background: #fff3cd; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #667eea; margin-bottom: 10px; }
        .btn-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 12px 30px; border-radius: 25px; }
        .btn-custom:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); color: white; }
    </style>
</head>
<body>
<div class="update-container">
    <div class="header">
        <h1><i class="fa fa-shield"></i> 防刷系统数据库更新</h1>
        <p class="text-muted">为抽奖系统添加强大的防刷功能</p>
    </div>

    <?php
    $updates_needed = [];
    $updates_done = [];
    
    try {
        // 检查并创建 ip_logs 表
        $check_ip_logs = $conn->query("SHOW TABLES LIKE 'ip_logs'");
        if ($check_ip_logs->num_rows == 0) {
            $updates_needed[] = 'ip_logs表';
            $sql = "CREATE TABLE ip_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip VARCHAR(45) NOT NULL,
                action VARCHAR(20) NOT NULL,
                timestamp DATETIME NOT NULL,
                date DATE NOT NULL,
                INDEX idx_ip_date (ip, date),
                INDEX idx_ip_timestamp (ip, timestamp)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if ($conn->query($sql)) {
                $updates_done[] = '✅ 成功创建 ip_logs 表（IP操作记录）';
            } else {
                $updates_done[] = '❌ 创建 ip_logs 表失败: ' . $conn->error;
            }
        } else {
            $updates_done[] = '✅ ip_logs 表已存在';
        }
        
        // 检查并创建 time_intervals 表
        $check_time_intervals = $conn->query("SHOW TABLES LIKE 'time_intervals'");
        if ($check_time_intervals->num_rows == 0) {
            $updates_needed[] = 'time_intervals表';
            $sql = "CREATE TABLE time_intervals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                identifier VARCHAR(100) NOT NULL,
                action VARCHAR(20) NOT NULL,
                last_action DATETIME NOT NULL,
                UNIQUE KEY unique_identifier_action (identifier, action)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if ($conn->query($sql)) {
                $updates_done[] = '✅ 成功创建 time_intervals 表（时间间隔控制）';
            } else {
                $updates_done[] = '❌ 创建 time_intervals 表失败: ' . $conn->error;
            }
        } else {
            $updates_done[] = '✅ time_intervals 表已存在';
        }
        
        // 初始化防刷设置（如果不存在）
        $check_settings = $conn->query("SELECT update1 FROM admins WHERE id = 1");
        if ($check_settings->num_rows > 0) {
            $current_settings = $check_settings->fetch_assoc();
            $settings_data = json_decode($current_settings['update1'] ?? '{}', true);
            
            if (empty($settings_data) || !isset($settings_data['ip_limit_enabled'])) {
                $updates_needed[] = '防刷设置初始化';
                $default_settings = json_encode([
                    'ip_limit_enabled' => 1,
                    'ip_daily_limit' => 5,
                    'time_interval' => 60,
                    'email_cooldown' => 60,
                    'max_attempts' => 10,
                    'ban_duration' => 3600
                ]);
                
                $sql = "UPDATE admins SET update1 = ? WHERE id = 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $default_settings);
                
                if ($stmt->execute()) {
                    $updates_done[] = '✅ 成功初始化防刷设置';
                } else {
                    $updates_done[] = '❌ 初始化防刷设置失败: ' . $conn->error;
                }
            } else {
                $updates_done[] = '✅ 防刷设置已存在';
            }
        }
        
        // 初始化封禁IP列表（如果不存在）
        $check_banned = $conn->query("SELECT update3 FROM admins WHERE id = 1");
        if ($check_banned->num_rows > 0) {
            $current_banned = $check_banned->fetch_assoc();
            if (empty($current_banned['update3'])) {
                $updates_needed[] = '封禁IP列表初始化';
                $empty_banned_list = json_encode([]);
                
                $sql = "UPDATE admins SET update3 = ? WHERE id = 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $empty_banned_list);
                
                if ($stmt->execute()) {
                    $updates_done[] = '✅ 成功初始化封禁IP列表';
                } else {
                    $updates_done[] = '❌ 初始化封禁IP列表失败: ' . $conn->error;
                }
            } else {
                $updates_done[] = '✅ 封禁IP列表已存在';
            }
        }
        
    } catch (Exception $e) {
        $updates_done[] = '❌ 更新过程中发生错误: ' . $e->getMessage();
    }
    
    $conn->close();
    ?>

    <div class="row">
        <div class="col-md-12">
            <h4><i class="fa fa-list"></i> 更新状态</h4>
            
            <?php if (empty($updates_needed)): ?>
                <div class="alert alert-success">
                    <h5><i class="fa fa-check-circle"></i> 系统已是最新状态</h5>
                    <p>防刷系统的所有组件都已正确安装和配置。</p>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <h5><i class="fa fa-info-circle"></i> 检测到需要更新的项目</h5>
                    <p>以下组件需要安装或更新：<?php echo implode('、', $updates_needed); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="update-results">
                <?php foreach ($updates_done as $update): ?>
                    <div class="status-item <?php echo strpos($update, '❌') !== false ? 'status-error' : ''; ?>">
                        <?php echo $update; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 30px;">
        <div class="col-md-12">
            <h4><i class="fa fa-shield"></i> 防刷功能说明</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>🛡️ 防护机制</strong></div>
                        <div class="panel-body">
                            <ul>
                                <li><strong>IP限制</strong>：限制单个IP每日抽奖次数</li>
                                <li><strong>时间间隔</strong>：防止快速连续操作</li>
                                <li><strong>邮件冷却</strong>：限制验证码发送频率</li>
                                <li><strong>自动封禁</strong>：异常行为自动封禁</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>⚙️ 管理功能</strong></div>
                        <div class="panel-body">
                            <ul>
                                <li><strong>实时统计</strong>：查看抽奖和用户统计</li>
                                <li><strong>封禁管理</strong>：查看和解封IP地址</li>
                                <li><strong>参数调整</strong>：灵活配置防刷参数</li>
                                <li><strong>日志记录</strong>：完整的操作记录</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center" style="margin-top: 30px;">
        <a href="admin.html" class="btn btn-custom">
            <i class="fa fa-cog"></i> 进入后台管理
        </a>
        <a href="index.php" class="btn btn-outline-primary" style="margin-left: 10px;">
            <i class="fa fa-home"></i> 返回首页
        </a>
    </div>

    <div class="alert alert-warning" style="margin-top: 20px;">
        <h5><i class="fa fa-exclamation-triangle"></i> 重要提示</h5>
        <ul>
            <li>防刷系统已集成到抽奖和邮件发送流程中</li>
            <li>可在后台"防刷设置"中调整各项参数</li>
            <li>建议根据实际使用情况调整限制参数</li>
            <li>系统会自动清理过期的封禁记录</li>
        </ul>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
