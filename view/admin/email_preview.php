<?php
$codeuse=0; $emailuse=0;$directoryPath = '../../';
include("../../core/xiaocore.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: xiao_login.php');
    exit;
}

// 获取管理员信息
$admin_query = $conn->prepare("SELECT * FROM admins WHERE username = ?");
$admin_query->bind_param("s", $_SESSION['admin_username']);
$admin_query->execute();
$admin_info = $admin_query->get_result()->fetch_assoc();

// 模拟中奖数据
$sample_data = [
    'prize_name' => '一等奖 - iPhone 15 Pro',
    'account' => '123456789@qq.com',
    'message' => '希望能中奖，谢谢！'
];

$subject = "🎉 恭喜您中奖了！- " . $admin_info['title'];
$htmlBody = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 15px;'>
    <div style='background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);'>
        <div style='text-align: center; margin-bottom: 30px;'>
            <h1 style='color: #667eea; margin: 0; font-size: 28px;'>🎉 恭喜中奖！</h1>
            <p style='color: #666; margin: 10px 0 0 0; font-size: 16px;'>您在我们的抽奖活动中获得了奖品</p>
        </div>

        <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
            <h3 style='color: #333; margin: 0 0 15px 0; font-size: 18px;'>🏆 中奖信息</h3>
            <p style='margin: 8px 0; color: #555;'><strong>奖品名称：</strong>" . htmlspecialchars($sample_data['prize_name']) . "</p>
            <p style='margin: 8px 0; color: #555;'><strong>中奖邮箱：</strong>" . htmlspecialchars($sample_data['account']) . "</p>
            <p style='margin: 8px 0; color: #555;'><strong>您的留言：</strong>" . htmlspecialchars($sample_data['message']) . "</p>
        </div>

        <div style='background: #e3f2fd; padding: 20px; border-radius: 8px; border-left: 4px solid #2196f3; margin-bottom: 20px;'>
            <h3 style='color: #1976d2; margin: 0 0 10px 0; font-size: 16px;'>📋 领奖须知</h3>
            <ul style='margin: 0; padding-left: 20px; color: #555;'>
                <li>请保存好此邮件作为中奖凭证</li>
                <li>我们将在3个工作日内与您联系</li>
                <li>请确保您的联系方式畅通</li>
                <li>如有疑问请及时联系我们</li>
            </ul>
        </div>

        <div style='text-align: center; padding-top: 20px; border-top: 1px solid #eee;'>
            <p style='color: #667eea; margin: 0; font-size: 16px; font-weight: bold;'>再次恭喜您！🎊</p>
            <p style='color: #999; margin: 5px 0 0 0; font-size: 14px;'>" . htmlspecialchars($admin_info['title']) . "</p>
        </div>
    </div>
</div>";

$altBody = "恭喜您中奖！\n\n奖品名称：" . $sample_data['prize_name'] . "\n中奖邮箱：" . $sample_data['account'] . "\n您的留言：" . $sample_data['message'] . "\n\n请保存好此邮件作为中奖凭证，我们将在3个工作日内与您联系。\n\n" . $admin_info['title'];

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>中奖邮件预览</title>
    <link rel="icon" href="favicon.ico" type="image/ico">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/materialdesignicons.min.css" rel="stylesheet">
    <link href="../../css/style.min.css" rel="stylesheet">
    <style>
        /* 性能优化 - 使用硬件加速 */
        * {
            -webkit-transform: translateZ(0);
            transform: translateZ(0);
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
        }

        /* 预加载动画 */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* 容器优化 */
        .preview-container {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            will-change: transform;
            transition: all 0.2s ease;
        }

        .email-frame {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            will-change: transform;
            transition: box-shadow 0.2s ease;
        }

        .email-frame:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .email-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .email-subject {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .email-from {
            font-size: 14px;
            color: #666;
            margin: 5px 0 0 0;
        }

        .email-body {
            padding: 0;
            background: white;
            will-change: scroll-position;
        }

        /* 代码块优化 */
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
            will-change: scroll-position;
            scrollbar-width: thin;
            scrollbar-color: #667eea #f1f1f1;
        }

        .code-block::-webkit-scrollbar {
            width: 8px;
        }

        .code-block::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .code-block::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 4px;
        }

        .code-block::-webkit-scrollbar-thumb:hover {
            background: #5a6fd8;
        }

        /* 标签页优化 */
        .tab-content {
            margin-top: 20px;
            will-change: contents;
        }

        .tab-pane {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .tab-pane.active {
            opacity: 1;
        }

        .nav-tabs {
            border-bottom: 2px solid #e9ecef;
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 8px 8px 0 0;
            color: #666;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            margin-right: 5px;
        }

        .nav-tabs .nav-link:hover {
            background-color: #f8f9fa;
            color: #667eea;
            transform: translateY(-2px);
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        /* 信息卡片优化 */
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
            will-change: transform;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.3);
        }

        /* 按钮优化 */
        .btn {
            transition: all 0.2s ease;
            will-change: transform;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        /* 响应式优化 */
        @media (max-width: 768px) {
            .preview-container {
                padding: 15px;
            }

            .info-card {
                padding: 20px;
            }

            .nav-tabs .nav-link {
                padding: 10px 15px;
                font-size: 14px;
            }
        }

        /* 平滑滚动 */
        html {
            scroll-behavior: smooth;
        }

        /* 内容淡入动画 */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
<!-- 加载动画 -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<div class="container-fluid p-t-15">
    <div class="row">
        <div class="col-xs-12">
            <div class="card fade-in">
                <div class="card-header">
                    <h4><i class="mdi mdi-email-open"></i> 中奖邮件预览</h4>
                    <small class="text-muted">查看发送给中奖用户的邮件样式和内容</small>
                </div>
                <div class="card-body">
                    <!-- 邮件信息卡片 -->
                    <div class="info-card">
                        <h5><i class="mdi mdi-information"></i> 邮件信息</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>邮件主题：</strong><?php echo htmlspecialchars($subject); ?></p>
                                <p><strong>发送方：</strong><?php echo htmlspecialchars($admin_info['title']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>示例收件人：</strong><?php echo htmlspecialchars($sample_data['account']); ?></p>
                                <p><strong>示例奖品：</strong><?php echo htmlspecialchars($sample_data['prize_name']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- 标签页导航 -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#html-preview" role="tab">
                                <i class="mdi mdi-web"></i> HTML 预览
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#text-preview" role="tab">
                                <i class="mdi mdi-text"></i> 纯文本版本
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#html-code" role="tab">
                                <i class="mdi mdi-code-tags"></i> HTML 源码
                            </a>
                        </li>
                    </ul>

                    <!-- 标签页内容 -->
                    <div class="tab-content">
                        <!-- HTML 预览 -->
                        <div class="tab-pane fade show active" id="html-preview" role="tabpanel">
                            <div class="preview-container">
                                <div class="email-frame">
                                    <div class="email-header">
                                        <div class="email-subject"><?php echo htmlspecialchars($subject); ?></div>
                                        <div class="email-from">发件人: <?php echo htmlspecialchars($admin_info['title']); ?> &lt;noreply@example.com&gt;</div>
                                    </div>
                                    <div class="email-body">
                                        <?php echo $htmlBody; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 纯文本预览 -->
                        <div class="tab-pane fade" id="text-preview" role="tabpanel">
                            <h5>纯文本版本（用于不支持HTML的邮件客户端）</h5>
                            <div class="code-block"><?php echo htmlspecialchars($altBody); ?></div>
                        </div>

                        <!-- HTML 源码 -->
                        <div class="tab-pane fade" id="html-code" role="tabpanel">
                            <h5>HTML 源码</h5>
                            <div class="code-block"><?php echo htmlspecialchars($htmlBody); ?></div>
                        </div>
                    </div>

                    <!-- 操作按钮 -->
                    <div class="text-center" style="margin-top: 30px;">
                        <a href="xiao_logs.php" class="btn btn-primary">
                            <i class="mdi mdi-arrow-left"></i> 返回抽奖记录
                        </a>
                        <a href="xiao_email.php" class="btn btn-info">
                            <i class="mdi mdi-settings"></i> 邮件配置
                        </a>
                    </div>

                    <!-- 说明信息 -->
                    <div class="alert alert-info" style="margin-top: 20px;">
                        <h5><i class="mdi mdi-lightbulb"></i> 说明</h5>
                        <ul>
                            <li><strong>HTML版本</strong>：支持HTML的邮件客户端（如Gmail、Outlook等）会显示精美的HTML格式</li>
                            <li><strong>纯文本版本</strong>：不支持HTML的邮件客户端会自动显示纯文本版本</li>
                            <li><strong>自动适配</strong>：邮件系统会根据收件人的邮件客户端自动选择合适的版本</li>
                            <li><strong>实际发送</strong>：在抽奖记录页面点击"发送中奖邮件"按钮即可发送给真实的中奖用户</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../js/jquery.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>
<script src="../../js/main.min.js"></script>

<script>
$(document).ready(function() {
    // 页面加载完成后隐藏加载动画
    setTimeout(function() {
        $('#loadingOverlay').fadeOut(300);

        // 触发淡入动画
        setTimeout(function() {
            $('.fade-in').addClass('visible');
        }, 100);
    }, 500);

    // 标签页切换优化
    $('.nav-tabs a').on('click', function(e) {
        e.preventDefault();

        // 移除所有活动状态
        $('.nav-tabs .nav-link').removeClass('active');
        $('.tab-pane').removeClass('active show');

        // 添加活动状态到当前标签
        $(this).addClass('active');

        // 显示对应内容
        var target = $(this).attr('href');
        $(target).addClass('active show');

        // 平滑滚动到内容区域
        $('html, body').animate({
            scrollTop: $('.tab-content').offset().top - 100
        }, 300);
    });

    // 代码块复制功能
    $('.code-block').each(function() {
        var $this = $(this);
        var copyBtn = $('<button class="btn btn-sm btn-outline-primary copy-btn" style="position: absolute; top: 10px; right: 10px; z-index: 10;">复制</button>');

        $this.css('position', 'relative').append(copyBtn);

        copyBtn.on('click', function() {
            var text = $this.text();

            // 创建临时文本区域
            var textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();

            try {
                document.execCommand('copy');
                copyBtn.text('已复制').removeClass('btn-outline-primary').addClass('btn-success');

                setTimeout(function() {
                    copyBtn.text('复制').removeClass('btn-success').addClass('btn-outline-primary');
                }, 2000);
            } catch (err) {
                console.error('复制失败:', err);
            }

            document.body.removeChild(textarea);
        });
    });

    // 邮件预览区域优化
    $('.email-body').on('scroll', function() {
        // 使用节流优化滚动性能
        clearTimeout(this.scrollTimeout);
        this.scrollTimeout = setTimeout(function() {
            // 滚动时的处理逻辑
        }, 16); // 约60fps
    });

    // 响应式优化
    function optimizeForMobile() {
        if ($(window).width() < 768) {
            $('.email-frame').css('transform', 'scale(0.9)');
            $('.preview-container').css('padding', '10px');
        } else {
            $('.email-frame').css('transform', 'scale(1)');
            $('.preview-container').css('padding', '20px');
        }
    }

    // 初始化和窗口大小改变时优化
    optimizeForMobile();
    $(window).on('resize', function() {
        clearTimeout(this.resizeTimeout);
        this.resizeTimeout = setTimeout(optimizeForMobile, 100);
    });

    // 预加载图片和资源
    var preloadImages = function() {
        var images = ['../../css/materialdesignicons.min.css'];
        images.forEach(function(src) {
            var img = new Image();
            img.src = src;
        });
    };

    preloadImages();

    // 性能监控
    if (window.performance && window.performance.timing) {
        var loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
        console.log('页面加载时间:', loadTime + 'ms');

        if (loadTime > 3000) {
            console.warn('页面加载较慢，建议优化');
        }
    }

    // 懒加载优化（如果有大量内容）
    var lazyLoad = function() {
        $('.tab-pane:not(.active)').each(function() {
            $(this).find('img, iframe').attr('data-src', function() {
                return $(this).attr('src');
            }).removeAttr('src');
        });
    };

    // 当标签页激活时加载内容
    $('.nav-tabs a').on('shown.bs.tab', function() {
        var target = $(this).attr('href');
        $(target).find('[data-src]').each(function() {
            $(this).attr('src', $(this).attr('data-src')).removeAttr('data-src');
        });
    });
});

// 页面卸载时清理
$(window).on('beforeunload', function() {
    // 清理定时器和事件监听器
    clearTimeout(window.scrollTimeout);
    clearTimeout(window.resizeTimeout);
});
</script>
</body>
</html>

<?php
$conn->close();
?>
