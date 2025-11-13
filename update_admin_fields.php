<?php
$emailuse = 0; 
$codeuse = 0; 
$directoryPath = './'; 
include("core/xiaocore.php");

// 检查是否已经添加了字段
$check_qqgroup = $conn->query("SHOW COLUMNS FROM admins LIKE 'qqgroup'");
$check_qqgrouplink = $conn->query("SHOW COLUMNS FROM admins LIKE 'qqgrouplink'");

$updates_needed = [];
$updates_done = [];

// 检查并添加 qqgroup 字段
if ($check_qqgroup->num_rows == 0) {
    $updates_needed[] = 'qqgroup';
    $sql = "ALTER TABLE admins ADD COLUMN qqgroup VARCHAR(50) DEFAULT '123456789'";
    if ($conn->query($sql)) {
        $updates_done[] = '✅ 成功添加 qqgroup 字段';
    } else {
        $updates_done[] = '❌ 添加 qqgroup 字段失败: ' . $conn->error;
    }
} else {
    $updates_done[] = '✅ qqgroup 字段已存在';
}

// 检查并添加 qqgrouplink 字段
if ($check_qqgrouplink->num_rows == 0) {
    $updates_needed[] = 'qqgrouplink';
    $sql = "ALTER TABLE admins ADD COLUMN qqgrouplink VARCHAR(500)";
    if ($conn->query($sql)) {
        // 添加字段后设置默认值
        $default_sql = "UPDATE admins SET qqgrouplink = 'https://qm.qq.com/cgi-bin/qm/qr?k=YOUR_GROUP_KEY' WHERE qqgrouplink IS NULL OR qqgrouplink = ''";
        $conn->query($default_sql);
        $updates_done[] = '✅ 成功添加 qqgrouplink 字段';
    } else {
        $updates_done[] = '❌ 添加 qqgrouplink 字段失败: ' . $conn->error;
    }
} else {
    $updates_done[] = '✅ qqgrouplink 字段已存在';
}

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>数据库字段更新</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            padding-top: 50px;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }
        .status-item {
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .status-success {
            background: rgba(16, 185, 129, 0.1);
            border-left-color: #10b981;
            color: #065f46;
        }
        .status-error {
            background: rgba(239, 68, 68, 0.1);
            border-left-color: #ef4444;
            color: #991b1b;
        }
        .btn-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h4 class="mb-0">
                            <i class="fas fa-database me-2"></i>
                            数据库字段更新
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-cogs fa-3x text-primary mb-3"></i>
                            <h5>QQ群功能字段更新</h5>
                            <p class="text-muted">为支持QQ群功能，需要在admins表中添加相关字段</p>
                        </div>

                        <div class="mb-4">
                            <h6><i class="fas fa-list-check me-2"></i>更新状态</h6>
                            <?php foreach ($updates_done as $update): ?>
                                <div class="status-item <?php echo strpos($update, '✅') !== false ? 'status-success' : 'status-error'; ?>">
                                    <?php echo $update; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (empty($updates_needed)): ?>
                            <div class="alert alert-success text-center">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h5>更新完成！</h5>
                                <p class="mb-0">所有必要的数据库字段都已存在，现在可以正常使用QQ群功能了。</p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <h5>更新进行中</h5>
                                <p class="mb-0">正在添加必要的数据库字段...</p>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="admin.html" class="btn btn-custom">
                                <i class="fas fa-arrow-left me-2"></i>返回后台管理
                            </a>
                            <a href="index.php" class="btn btn-outline-primary ms-2">
                                <i class="fas fa-home me-2"></i>返回首页
                            </a>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded">
                            <h6><i class="fas fa-info-circle me-2"></i>使用说明</h6>
                            <ol class="mb-0 small">
                                <li>更新完成后，请前往 <strong>后台管理 → 功能设置 → 网站信息</strong></li>
                                <li>在页面底部找到 <strong>QQ群号</strong> 和 <strong>QQ群链接</strong> 字段</li>
                                <li>填入您的真实QQ群号和群链接</li>
                                <li>保存设置后，前端页面将显示您配置的群信息</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
