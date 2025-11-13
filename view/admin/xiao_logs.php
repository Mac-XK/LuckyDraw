<?php
$codeuse=0; $emailuse=1;$directoryPath = '../../';
include("../../core/xiaocore.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: xiao_login.php');
    exit;
}

$delete_message = null;

// 处理发送中奖邮件
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $log_id = intval($_POST['log_id']);
    $custom_content = trim($_POST['custom_content'] ?? '');

    // 获取中奖记录详情
    $stmt = $conn->prepare("SELECT logs.account, logs.message, prizes.name AS prize_name
                           FROM lottery_logs AS logs
                           LEFT JOIN prizes ON logs.prize_id = prizes.id
                           WHERE logs.id = ? AND logs.prize_id IS NOT NULL");
    $stmt->bind_param("i", $log_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $record = $result->fetch_assoc();

        // 获取管理员信息（邮件配置）
        $admin_query = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $admin_query->bind_param("s", $_SESSION['admin_username']);
        $admin_query->execute();
        $admin_info = $admin_query->get_result()->fetch_assoc();

        if ($admin_info['emailsend'] == 1) {
            $subject = "🎉 恭喜您中奖了！- " . $admin_info['title'];

            // 构建自定义内容区域
            $customContentHtml = '';
            if (!empty($custom_content)) {
                $customContentHtml = "
                    <div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin-bottom: 20px;'>
                        <h3 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>💌 特别通知</h3>
                        <p style='margin: 0; color: #856404; line-height: 1.6;'>" . nl2br(htmlspecialchars($custom_content)) . "</p>
                    </div>";
            }

            $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 15px;'>
                <div style='background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);'>
                    <div style='text-align: center; margin-bottom: 30px;'>
                        <h1 style='color: #667eea; margin: 0; font-size: 28px;'>🎉 恭喜中奖！</h1>
                        <p style='color: #666; margin: 10px 0 0 0; font-size: 16px;'>您在我们的抽奖活动中获得了奖品</p>
                    </div>

                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                        <h3 style='color: #333; margin: 0 0 15px 0; font-size: 18px;'>🏆 中奖信息</h3>
                        <p style='margin: 8px 0; color: #555;'><strong>奖品名称：</strong>" . htmlspecialchars($record['prize_name']) . "</p>
                        <p style='margin: 8px 0; color: #555;'><strong>中奖邮箱：</strong>" . htmlspecialchars($record['account']) . "</p>
                        <p style='margin: 8px 0; color: #555;'><strong>您的留言：</strong>" . htmlspecialchars($record['message']) . "</p>
                    </div>

                    " . $customContentHtml . "

                    <div style='background: #e3f2fd; padding: 20px; border-radius: 8px; border-left: 4px solid #2196f3; margin-bottom: 20px;'>
                        <h3 style='color: #1976d2; margin: 0 0 10px 0; font-size: 16px;'>📋 领奖须知</h3>
                        <ul style='margin: 0; padding-left: 20px; color: #555;'>
                            <li>请保存好此邮件作为中奖凭证</li>
                            <li>我们将在3个工作日内与您联系</li>
                            <li>请确保您的联系方式畅通</li>
                            <li>如有疑问，请及时联系我们</li>
                        </ul>
                    </div>

                    <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                        <p style='color: #999; margin: 0; font-size: 14px;'>感谢您参与我们的抽奖活动！</p>
                        <p style='color: #999; margin: 5px 0 0 0; font-size: 14px;'>" . htmlspecialchars($admin_info['title']) . "</p>
                    </div>
                </div>
            </div>";

            $altBody = "恭喜您中奖！\n\n奖品名称：" . $record['prize_name'] . "\n中奖邮箱：" . $record['account'] . "\n您的留言：" . $record['message'];

            if (!empty($custom_content)) {
                $altBody .= "\n\n特别通知：\n" . $custom_content;
            }

            $altBody .= "\n\n请保存好此邮件作为中奖凭证，我们将在3个工作日内与您联系。\n\n" . $admin_info['title'];

            $send_result = send($record['account'], '中奖用户', $subject, $htmlBody, $altBody, $admin_info);

            if ($send_result === true) {
                $delete_message = "✅ 中奖邮件发送成功！已发送到：" . $record['account'];
            } else {
                $delete_message = "❌ 邮件发送失败：" . $send_result;
            }
        } else {
            $delete_message = "❌ 邮件功能未开启，请先在邮件配置中开启邮件发送功能";
        }
    } else {
        $delete_message = "❌ 未找到该中奖记录或该记录不是中奖记录";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_logs'])) {
    if ($conn->query("DELETE FROM lottery_logs") === TRUE) {
        $delete_message = "记录已经清空";
    } else {
        $delete_message = "清空记录失败: " . $conn->error;
    }
}


$sql = "SELECT logs.id, logs.account, logs.date, logs.message, prizes.name AS prize_name 
        FROM lottery_logs AS logs 
        LEFT JOIN prizes ON logs.prize_id = prizes.id 
        ORDER BY logs.date DESC, logs.message DESC, logs.id DESC";

$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title>抽奖记录</title>
    <link rel="icon" href="favicon.ico" type="image/ico">
    <meta name="keywords" content="小猫咪抽奖系统,年会抽奖系统,节日抽奖系统,双十一活动,618活动,双十二活动">
    <meta name="description" content="小猫咪抽奖系统，一款开源免费的php抽奖系统，可用于年会抽奖，节日抽奖等等，支持自定义奖品概率和数量，页面简介美观，操作容易">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/materialdesignicons.min.css" rel="stylesheet">
    <link href="../../css/style.min.css" rel="stylesheet">
    <style>
        .alert-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .alert-danger { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .label { display: inline-block; padding: 0.25em 0.6em; font-size: 75%; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.25rem; }
        .label-success { background-color: #5cb85c; color: #fff; }
        .label-default { background-color: #777; color: #fff; }
        .btn-xs { padding: 1px 5px; font-size: 12px; line-height: 1.5; border-radius: 3px; }
        .table td { vertical-align: middle; }
        .card-header small { display: block; margin-top: 5px; }
    </style>
</head>

<body>
<div class="container-fluid p-t-15">
    <div class="row">
        <div class="col-xs-12">
            <div class="card">
                <div class="card-header">
                    <h4>抽奖记录</h4>
                    <small class="text-muted">
                        <i class="mdi mdi-information"></i>
                        点击"发送中奖邮件"可以向中奖用户发送精美的中奖通知邮件，需要先在邮件配置中开启邮件功能
                        <a href="email_preview.php" class="btn btn-info btn-xs" style="margin-left: 10px;">
                            <i class="mdi mdi-eye"></i> 预览邮件样式
                        </a>
                    </small>
                </div>
                <div class="card-body">
                    <?php if ($delete_message !== null): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($delete_message); ?></div>
                    <?php endif; ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>记录ID</th>
                                <th>账号</th>
                                <th>奖品名称</th>
                                <th>用户留言</th>
                                <th>抽奖日期</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['account']); ?></td>
                                        <td>
                                            <?php if ($row['prize_name']): ?>
                                                <span class="label label-success"><?php echo htmlspecialchars($row['prize_name']); ?></span>
                                            <?php else: ?>
                                                <span class="label label-default">未中奖</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['message']); ?></td>
                                        <td><?php echo $row['date']; ?></td>
                                        <td>
                                            <?php if ($row['prize_name']): ?>
                                                <button type="button" class="btn btn-primary btn-xs" onclick="showEmailModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['account'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['prize_name'], ENT_QUOTES); ?>')">
                                                    <i class="mdi mdi-email-send"></i> 发送中奖邮件
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">暂无记录</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <form method="POST" onsubmit="return confirm('确定要清空所有抽奖记录吗？');" style="display: inline;">
                        <button type="submit" name="clear_logs" class="btn btn-danger">清空抽奖记录</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 发送中奖邮件模态框 -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="emailModalLabel">
                    <i class="mdi mdi-email-send"></i> 发送中奖邮件
                </h4>
            </div>
            <form method="POST" id="emailForm">
                <div class="modal-body">
                    <input type="hidden" name="log_id" id="modal_log_id">

                    <div class="alert alert-info">
                        <h5><i class="mdi mdi-information"></i> 中奖信息</h5>
                        <p><strong>收件人：</strong><span id="modal_email"></span></p>
                        <p><strong>奖品：</strong><span id="modal_prize"></span></p>
                    </div>

                    <div class="form-group">
                        <label for="custom_content">
                            <i class="mdi mdi-message-text"></i> 自定义中奖内容
                            <small class="text-muted">（可选，将显示在邮件的特别通知区域）</small>
                        </label>
                        <textarea class="form-control" name="custom_content" id="custom_content" rows="4"
                                  placeholder="请输入要发送给中奖用户的特别通知内容，例如：&#10;&#10;恭喜您获得一等奖！&#10;请在7天内联系我们领取奖品。&#10;联系电话：400-123-4567&#10;联系时间：工作日 9:00-18:00"></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i>
                        <strong>注意：</strong>邮件发送后无法撤回，请确认内容无误后再发送。
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="mdi mdi-close"></i> 取消
                    </button>
                    <button type="submit" name="send_email" class="btn btn-primary">
                        <i class="mdi mdi-email-send"></i> 发送邮件
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="../../js/jquery.min.js"></script>
<script type="text/javascript" src="../../js/bootstrap.min.js"></script>
<script type="text/javascript" src="../../js/main.min.js"></script>

<script>
function showEmailModal(logId, email, prizeName) {
    $('#modal_log_id').val(logId);
    $('#modal_email').text(email);
    $('#modal_prize').text(prizeName);
    $('#custom_content').val('');
    $('#emailModal').modal('show');
}

// 表单提交确认
$('#emailForm').on('submit', function(e) {
    var email = $('#modal_email').text();
    var customContent = $('#custom_content').val().trim();

    var confirmMessage = '确定要发送中奖邮件给 ' + email + ' 吗？';
    if (customContent) {
        confirmMessage += '\n\n自定义内容：\n' + customContent;
    }

    if (!confirm(confirmMessage)) {
        e.preventDefault();
        return false;
    }
});
</script>
</body>
</html>

<?php
$conn->close();
?>