<?php $emailuse=0; $codeuse=0; $directoryPath = './'; include("core/xiaocore.php"); ?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo htmlspecialchars($info['title']); ?></title>
    <link rel="icon" href="favicon.ico" type="image/ico">
    <meta name="keywords" content="<?php echo htmlspecialchars($info['keywords']); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($info['description']); ?>">

    <!-- 现代化CSS框架 -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* 主容器 */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* 卡片样式 */
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); }

        /* 标题样式 */
        .main-title {
            text-align: center;
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        /* 导航标签 */
        .nav-tabs {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .nav-tab {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid transparent;
            border-radius: 50px;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        .nav-tab:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        .nav-tab.active {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 5px 20px rgba(255, 255, 255, 0.2);
        }

        /* 表单样式 */
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            color: white;
            font-weight: 500;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        .form-input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }
        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        /* 按钮样式 */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            font-size: 0.9rem;
        }
        .btn-primary {
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.3));
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.4));
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.2);
        }

        /* 内容面板 */
        .content-panel {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        .content-panel.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* 公告内容文字颜色修复 */
        .announcement-content * {
            color: #ffffff !important;
            background: transparent !important;
        }
        .announcement-content p {
            color: #ffffff !important;
            background: transparent !important;
        }
        .announcement-content div {
            color: #ffffff !important;
            background: transparent !important;
        }
        .announcement-content span {
            color: #ffffff !important;
            background: transparent !important;
        }
        .announcement-content h1,
        .announcement-content h2,
        .announcement-content h3,
        .announcement-content h4,
        .announcement-content h5,
        .announcement-content h6 {
            color: #ffffff !important;
            background: transparent !important;
        }
        .announcement-content strong,
        .announcement-content b {
            color: #ffffff !important;
            background: transparent !important;
        }

        /* AI工具奖品按钮样式 */
        .prize-item {
            display: inline-block;
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8)) !important;
            color: #ffffff !important;
            padding: 6px 12px !important;
            margin: 3px 4px !important;
            border-radius: 15px !important;
            font-weight: 500 !important;
            font-size: 0.8rem !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3) !important;
            transition: all 0.3s ease !important;
            white-space: nowrap !important;
            max-width: calc(50% - 8px) !important;
            text-align: center !important;
        }
        .prize-item:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5) !important;
        }

        /* 手机端优化 */
        @media (max-width: 768px) {
            .prize-item {
                font-size: 0.75rem !important;
                padding: 5px 10px !important;
                margin: 2px 3px !important;
                border-radius: 12px !important;
                max-width: calc(48% - 6px) !important;
            }
        }

        @media (max-width: 480px) {
            .prize-item {
                font-size: 0.7rem !important;
                padding: 4px 8px !important;
                margin: 2px !important;
                max-width: calc(45% - 4px) !important;
            }
        }

        /* 商品展示样式 */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            background: rgba(255, 255, 255, 0.18);
        }

        .product-card:hover::before {
            opacity: 1;
        }

        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .product-category {
            display: inline-flex;
            align-items: center;
            background: rgba(59, 130, 246, 0.2);
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(59, 130, 246, 0.4);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .product-price {
            color: #fbbf24;
            font-size: 1.5rem;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(251, 191, 36, 0.3);
        }

        .product-name {
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.3;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .product-description {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 24px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .buy-button {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .buy-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .buy-button:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 50%, #ec4899 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .buy-button:hover::before {
            left: 100%;
        }

        .buy-button:active {
            transform: translateY(-1px);
        }

        /* 分类筛选样式 */
        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .category-filter {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .category-filter:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateY(-2px);
        }

        .category-filter.active {
            background: rgba(102, 126, 234, 0.3);
            border-color: rgba(102, 126, 234, 0.5);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        /* 响应式优化 */
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .product-card {
                padding: 16px;
            }

            .product-header {
                flex-direction: column;
                gap: 6px;
                align-items: flex-start;
            }

            .product-price {
                font-size: 1.2rem;
            }

            .product-name {
                font-size: 1.1rem;
                margin-bottom: 8px;
            }

            .product-description {
                font-size: 0.85rem;
                margin-bottom: 16px;
                -webkit-line-clamp: 2;
            }

            .buy-button {
                padding: 10px 16px;
                font-size: 0.9rem;
            }

            .category-filters {
                gap: 6px;
                margin-bottom: 16px;
            }

            .category-filter {
                padding: 6px 10px;
                font-size: 0.75rem;
                border-radius: 16px;
                white-space: nowrap;
            }

            .category-filter i {
                margin-right: 3px !important;
            }
        }

        /* 小屏手机优化 */
        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .product-card {
                padding: 12px;
            }

            .product-name {
                font-size: 1rem;
                margin-bottom: 6px;
            }

            .product-description {
                font-size: 0.8rem;
                margin-bottom: 12px;
                -webkit-line-clamp: 2;
            }

            .buy-button {
                padding: 8px 12px;
                font-size: 0.8rem;
            }

            .category-filters {
                gap: 4px;
                justify-content: flex-start;
                overflow-x: auto;
                padding-bottom: 8px;
                -webkit-overflow-scrolling: touch;
                flex-wrap: nowrap !important;
                display: flex !important;
            }

            .category-filter {
                padding: 4px 8px;
                font-size: 0.65rem;
                border-radius: 12px;
                flex-shrink: 0;
                white-space: nowrap;
                min-width: auto;
            }

            .category-filter i {
                margin-right: 2px !important;
                font-size: 0.6rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- 头部标题 -->
        <div class="text-center mb-10">
            <h1 class="main-title"><?php echo htmlspecialchars($info['title']); ?></h1>
            <p class="subtitle">简单、快速、公平的抽奖体验</p>
        </div>

        <!-- 导航标签 -->
        <div class="nav-tabs">
            <div class="nav-tab active" data-target="announcement">
                <i class="fas fa-bullhorn mr-2"></i>活动公告
            </div>
            <div class="nav-tab" data-target="lottery">
                <i class="fas fa-gift mr-2"></i>开始抽奖
            </div>
            <div class="nav-tab" data-target="check">
                <i class="fas fa-search mr-2"></i>查询记录
            </div>
            <div class="nav-tab" data-target="shop">
                <i class="fas fa-shopping-cart mr-2"></i>商品商城
            </div>
        </div>

        <!-- 内容区域 -->

        <!-- 活动公告 -->
        <div id="announcement" class="content-panel active">
            <div class="card">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center;">
                    <i class="fas fa-bullhorn" style="margin-right: 8px; color: #fbbf24;"></i>
                    活动公告
                </h2>
                <div class="announcement-content" style="background: rgba(0, 0, 0, 0.5); border-radius: 10px; padding: 20px; line-height: 1.6; font-size: 0.9rem; border: 1px solid rgba(255, 255, 255, 0.3); margin-bottom: 20px;">
                    <?php echo ($info['announcement']); ?>
                </div>

                <!-- 加群获取获奖名单 -->
                <div style="background: rgba(18, 216, 250, 0.1); border: 1px solid rgba(18, 216, 250, 0.3); border-radius: 12px; padding: 20px;">
                    <h3 style="color: white; font-size: 1.1rem; font-weight: 600; margin-bottom: 15px; display: flex; align-items: center;">
                        <i class="fab fa-qq" style="margin-right: 8px; color: #12d8fa;"></i>
                        加群获取获奖名单
                    </h3>
                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <p style="color: rgba(255, 255, 255, 0.8); font-size: 0.9rem; margin-bottom: 10px;">
                                加入官方QQ群，第一时间获取完整获奖名单
                            </p>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <span style="color: rgba(255, 255, 255, 0.7); font-size: 0.85rem;">QQ群号：</span>
                                <span style="color: #12d8fa; font-weight: 600; font-family: 'Courier New', monospace; font-size: 1.1rem;" id="qq-group-number-small">
                                    <?php echo htmlspecialchars($info['qqgroup'] ?? '123456789'); ?>
                                </span>
                                <button onclick="copyGroupNumberSmall()" style="background: rgba(18, 216, 250, 0.2); color: #12d8fa; border: 1px solid rgba(18, 216, 250, 0.4); padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; cursor: pointer; transition: all 0.3s ease;">
                                    <i class="fas fa-copy" style="margin-right: 3px;"></i>复制
                                </button>
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <a href="<?php echo htmlspecialchars($info['qqgrouplink'] ?? 'https://qm.qq.com/cgi-bin/qm/qr?k=YOUR_GROUP_KEY'); ?>" target="_blank"
                               style="background: linear-gradient(45deg, #12d8fa, #1e40af); color: white; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 500; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fab fa-qq"></i>一键加群
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 抽奖区域 -->
        <div id="lottery" class="content-panel">
            <div class="card">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center;">
                    <i class="fas fa-gift" style="margin-right: 8px; color: #f472b6;"></i>
                    开始抽奖
                </h2>

                <form id="lotteryForm">
                    <!-- 邮箱输入 -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope" style="margin-right: 8px;"></i>邮箱地址
                        </label>
                        <input type="email" name="email" id="email" required class="form-input" placeholder="请输入您的QQ邮箱（纯数字@qq.com）" pattern="[0-9]+@qq\.com" title="请输入正确的QQ邮箱格式，如：123456789@qq.com">
                    </div>

                    <!-- 卡密输入（条件显示） -->
                    <?php if ($info['update1'] == 1): ?>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key" style="margin-right: 8px;"></i>兑换码/卡密
                        </label>
                        <input type="text" name="kami" id="kami" required class="form-input" placeholder="请输入兑换码或卡密">
                    </div>
                    <?php endif; ?>

                    <!-- 验证码区域（条件显示） -->
                    <?php if ($info['emailsend'] == 1): ?>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-shield-alt" style="margin-right: 8px;"></i>邮箱验证码
                        </label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" name="verification-code" id="verification-code" required class="form-input" placeholder="输入验证码" style="flex: 1;">
                            <button type="button" id="send-code" class="btn btn-primary" style="white-space: nowrap;">发送验证码</button>
                        </div>
                        <?php if ($info['cfcode'] == 1): ?>
                        <div style="margin-top: 15px;">
                            <div class="cf-turnstile" data-sitekey="<?php echo ($info['sitekey']); ?>" data-callback="turnstileCallback"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- 联系方式 -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-comment" style="margin-right: 8px;"></i>联系方式备注
                        </label>
                        <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                            <div style="color: #93c5fd; font-size: 0.85rem; line-height: 1.4;">
                                <i class="fas fa-info-circle" style="margin-right: 6px; color: #60a5fa;"></i>
                                <strong>温馨提示：</strong>请填写您的联系方式（QQ、微信、手机号等），方便中奖后联系您领奖
                            </div>
                        </div>
                        <textarea name="text" id="text" rows="3" class="form-input" placeholder="请输入您的联系方式..." style="resize: none;"></textarea>
                    </div>

                    <!-- 抽奖按钮 -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 0.95rem; padding: 14px;">
                        <i class="fas fa-dice" style="margin-right: 8px;"></i>立即抽奖
                    </button>
                </form>
            </div>
        </div>

        <!-- 查询记录区域 -->
        <div id="check" class="content-panel">
            <div class="card">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center;">
                    <i class="fas fa-search" style="margin-right: 8px; color: #3b82f6;"></i>
                    查询中奖记录
                </h2>

                <form id="checkForm">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope" style="margin-right: 8px;"></i>查询邮箱
                        </label>
                        <div style="display: flex; gap: 10px;">
                            <input type="email" name="check_email" id="check_email" required class="form-input" placeholder="输入要查询的QQ邮箱地址（纯数字@qq.com）" style="flex: 1;" pattern="[0-9]+@qq\.com" title="请输入正确的QQ邮箱格式，如：123456789@qq.com">
                            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                                <i class="fas fa-search" style="margin-right: 8px;"></i>查询
                            </button>
                        </div>
                    </div>
                </form>

                <!-- 查询结果区域 -->
                <div id="check-results" style="display: none; margin-top: 25px;">
                    <h3 style="color: white; font-size: 1.2rem; font-weight: 600; margin-bottom: 20px;">查询结果</h3>
                    <div id="results-content"></div>
                </div>
            </div>
        </div>

        <!-- 商品商城区域 -->
        <div id="shop" class="content-panel">
            <div class="card">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center;">
                    <i class="fas fa-shopping-cart" style="margin-right: 8px; color: #10b981;"></i>
                    商品商城
                </h2>

                <!-- 分类筛选 -->
                <div class="category-filters" id="category-filters">
                    <!-- 分类将通过JavaScript动态加载 -->
                </div>

                <!-- 商品网格 -->
                <div class="products-grid" id="products-grid">
                    <!-- 商品将通过JavaScript动态加载 -->
                </div>
            </div>
        </div>


    </div>

    <!-- 底部信息 -->
    <div style="text-align: center; margin-top: 40px; padding: 20px; color: rgba(255, 255, 255, 0.7);">
        <?php echo ($info['foot']); ?>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let cfResponse = '';

        // Turnstile回调
        function turnstileCallback(token) {
            cfResponse = token;
        }

        // QQ邮箱格式验证函数
        function validateQQEmail(email) {
            var qqEmailPattern = /^[0-9]+@qq\.com$/;
            return qqEmailPattern.test(email);
        }

        $(document).ready(function() {
            // 导航标签切换
            $('.nav-tab').click(function() {
                const target = $(this).data('target');

                // 切换标签状态
                $('.nav-tab').removeClass('active');
                $(this).addClass('active');

                // 切换内容面板
                $('.content-panel').removeClass('active');
                $('#' + target).addClass('active');
            });



            // 发送验证码
            $('#send-code').click(function() {
                const email = $('#email').val();
                if (!email) {
                    Swal.fire({
                        icon: 'warning',
                        title: '提示',
                        text: '请先输入邮箱地址',
                        confirmButtonColor: '#667eea'
                    });
                    return;
                }

                // 验证QQ邮箱格式
                if (!validateQQEmail(email)) {
                    Swal.fire({
                        icon: 'error',
                        title: '邮箱格式错误',
                        text: '请输入正确的QQ邮箱格式，如：123456789@qq.com（纯数字@qq.com）',
                        confirmButtonColor: '#667eea'
                    });
                    return;
                }

                <?php if ($info['cfcode'] == 1): ?>
                if (!cfResponse) {
                    Swal.fire({
                        icon: 'warning',
                        title: '提示',
                        text: '请完成人机验证',
                        confirmButtonColor: '#667eea'
                    });
                    return;
                }
                <?php endif; ?>

                const btn = $(this);
                btn.prop('disabled', true).text('发送中...');

                $.ajax({
                    url: 'view/index/xiao_email.php?act=send',
                    type: 'POST',
                    data: { email: email, cf: cfResponse },
                    dataType: 'json',
                    success: function(response) {
                        if (response.code === 1) {
                            Swal.fire({
                                icon: 'success',
                                title: '发送成功',
                                text: '验证码已发送到您的邮箱，请查收',
                                confirmButtonColor: '#667eea'
                            });

                            // 倒计时
                            let countdown = 60;
                            const timer = setInterval(() => {
                                btn.text(`${countdown}秒后重发`);
                                countdown--;
                                if (countdown < 0) {
                                    clearInterval(timer);
                                    btn.prop('disabled', false).text('发送验证码');
                                }
                            }, 1000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '发送失败',
                                text: response.result || '发送失败，请稍后重试',
                                confirmButtonColor: '#667eea'
                            });
                            btn.prop('disabled', false).text('发送验证码');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: '网络错误',
                            text: '无法连接到服务器，请稍后重试',
                            confirmButtonColor: '#667eea'
                        });
                        btn.prop('disabled', false).text('发送验证码');
                    }
                });
            });

            // 抽奖表单提交
            $('#lotteryForm').submit(function(e) {
                e.preventDefault();

                const email = $('#email').val();

                // 验证QQ邮箱格式
                if (!validateQQEmail(email)) {
                    Swal.fire({
                        icon: 'error',
                        title: '邮箱格式错误',
                        text: '请输入正确的QQ邮箱格式，如：123456789@qq.com（纯数字@qq.com）',
                        confirmButtonColor: '#667eea'
                    });
                    return;
                }

                const btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>抽奖中...');

                $.ajax({
                    url: 'view/index/modern_lottery.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (response.won) {
                                // 中奖了
                                Swal.fire({
                                    icon: 'success',
                                    title: '🎉 恭喜中奖！',
                                    html: `<div class="text-center">
                                        <div class="text-6xl mb-4">🎁</div>
                                        <h3 class="text-xl font-bold mb-2">您获得了：${response.prize}</h3>
                                        <p class="text-gray-600">请保存好这个页面，我们会根据您留下的联系方式与您联系！</p>
                                    </div>`,
                                    confirmButtonColor: '#667eea',
                                    confirmButtonText: '太棒了！'
                                });
                            } else {
                                // 没中奖
                                Swal.fire({
                                    icon: 'info',
                                    title: '😊 谢谢参与',
                                    html: `<div class="text-center">
                                        <div class="text-6xl mb-4">🍀</div>
                                        <p class="text-gray-600">很遗憾这次没有中奖，不要灰心！</p>
                                        <p class="text-gray-600">每天都有新的机会哦~</p>
                                    </div>`,
                                    confirmButtonColor: '#667eea',
                                    confirmButtonText: '下次再来'
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '抽奖失败',
                                text: response.message || '请检查输入信息是否正确',
                                confirmButtonColor: '#667eea'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: '网络错误',
                            text: '抽奖失败，请稍后重试',
                            confirmButtonColor: '#667eea'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-dice" style="margin-right: 10px;"></i>立即抽奖');
                    }
                });
            });

            // 查询表单提交
            $('#checkForm').submit(function(e) {
                e.preventDefault();

                const email = $('#check_email').val();

                // 验证QQ邮箱格式
                if (!validateQQEmail(email)) {
                    Swal.fire({
                        icon: 'error',
                        title: '邮箱格式错误',
                        text: '请输入正确的QQ邮箱格式，如：123456789@qq.com（纯数字@qq.com）',
                        confirmButtonColor: '#667eea'
                    });
                    return;
                }

                const btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>查询中...');

                $.ajax({
                    url: 'view/index/modern_check.php',
                    type: 'POST',
                    data: { email: email },
                    dataType: 'json',
                    success: function(response) {
                        const $results = $('#check-results');
                        const $content = $('#results-content');

                        if (response.success) {
                            // 显示统计信息
                            let statsHtml = `
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px;">
                                    <div style="background: rgba(59, 130, 246, 0.2); border-radius: 12px; padding: 20px; text-align: center;">
                                        <div style="font-size: 1.8rem; font-weight: bold; color: white;">${response.statistics.total_draws}</div>
                                        <div style="color: rgba(147, 197, 253, 1); font-size: 0.9rem;">总抽奖次数</div>
                                    </div>
                                    <div style="background: rgba(34, 197, 94, 0.2); border-radius: 12px; padding: 20px; text-align: center;">
                                        <div style="font-size: 1.8rem; font-weight: bold; color: white;">${response.statistics.total_wins}</div>
                                        <div style="color: rgba(134, 239, 172, 1); font-size: 0.9rem;">总中奖次数</div>
                                    </div>
                                    <div style="background: rgba(168, 85, 247, 0.2); border-radius: 12px; padding: 20px; text-align: center;">
                                        <div style="font-size: 1.8rem; font-weight: bold; color: white;">${response.statistics.today_draws}</div>
                                        <div style="color: rgba(196, 181, 253, 1); font-size: 0.9rem;">今日抽奖次数</div>
                                    </div>
                                </div>
                            `;

                            if (response.records.length > 0) {
                                let tableHtml = `
                                    <div style="background: rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 20px;">
                                        <h4 style="color: white; font-size: 1.2rem; font-weight: 600; margin-bottom: 20px;">🏆 中奖记录</h4>
                                        <div style="overflow-x: auto;">
                                            <table style="width: 100%; color: white;">
                                                <thead>
                                                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.3);">
                                                        <th style="text-align: left; padding: 12px 8px;">奖品名称</th>
                                                        <th style="text-align: left; padding: 12px 8px;">联系方式</th>
                                                        <th style="text-align: left; padding: 12px 8px;">中奖日期</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                `;

                                response.records.forEach(record => {
                                    tableHtml += `
                                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                            <td style="padding: 12px 8px;">
                                                <span style="background: #fbbf24; color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.9rem;">
                                                    ${record.prize_name}
                                                </span>
                                            </td>
                                            <td style="padding: 12px 8px; color: rgba(255, 255, 255, 0.8);">${record.message || '未填写'}</td>
                                            <td style="padding: 12px 8px; color: rgba(255, 255, 255, 0.8);">${record.date}</td>
                                        </tr>
                                    `;
                                });

                                tableHtml += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                `;

                                $content.html(statsHtml + tableHtml);
                            } else {
                                $content.html(statsHtml + `
                                    <div style="background: rgba(251, 191, 36, 0.2); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 12px; padding: 30px; text-align: center;">
                                        <div style="font-size: 3rem; margin-bottom: 10px;">🍀</div>
                                        <p style="color: rgba(254, 240, 138, 1); font-size: 1.1rem;">该邮箱暂无中奖记录</p>
                                        <p style="color: rgba(251, 191, 36, 1); font-size: 0.9rem; margin-top: 8px;">继续努力，好运就在下一次！</p>
                                    </div>
                                `);
                            }
                        } else {
                            $content.html(`
                                <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 20px;">
                                    <p style="color: rgba(252, 165, 165, 1);">${response.message}</p>
                                </div>
                            `);
                        }

                        $results.show();
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: '查询失败',
                            text: '无法查询记录，请稍后重试',
                            confirmButtonColor: '#667eea'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-search" style="margin-right: 8px;"></i>查询');
                    }
                });
            });

            // 为AI工具奖品添加按钮样式
            function stylePrizeItems() {
                $('.announcement-content').find('*').each(function() {
                    let html = $(this).html();
                    if (html && typeof html === 'string') {
                        // 替换AI工具名称为带样式的span
                        html = html.replace(/🖱️ Cursor/g, '<span class="prize-item">🖱️ Cursor</span>');
                        html = html.replace(/🚀 Augment/g, '<span class="prize-item">🚀 Augment</span>');
                        html = html.replace(/💎 Gemini/g, '<span class="prize-item">💎 Gemini</span>');
                        html = html.replace(/🤖 GPT/g, '<span class="prize-item">🤖 GPT</span>');
                        html = html.replace(/⚡ Warp/g, '<span class="prize-item">⚡ Warp</span>');
                        html = html.replace(/🧠 AI/g, '<span class="prize-item">🧠 AI</span>');
                        $(this).html(html);
                    }
                });
            }

            // 页面加载完成后执行样式化
            setTimeout(stylePrizeItems, 500);

            // 当切换到公告标签时也执行样式化
            $('.nav-tab[data-target="announcement"]').click(function() {
                setTimeout(stylePrizeItems, 200);
            });

            // 商品数据缓存
            let products = [];
            let currentCategory = 'all';
            let categories = [];

            // 从API加载分类数据
            function loadCategories() {
                $.ajax({
                    url: 'view/index/get_categories.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            categories = response.data;
                            renderCategoryFilters();
                        } else {
                            console.error('加载分类失败：', response.message);
                            // 显示默认的"全部商品"筛选器
                            renderCategoryFilters([]);
                        }
                    },
                    error: function() {
                        console.error('网络错误，无法加载分类');
                        // 显示默认的"全部商品"筛选器
                        renderCategoryFilters([]);
                    }
                });
            }

            // 渲染分类筛选器
            function renderCategoryFilters(categoriesToShow = categories) {
                const filtersContainer = $('#category-filters');
                filtersContainer.empty();

                // 添加"全部商品"选项
                filtersContainer.append(`
                    <div class="category-filter active" data-category="all">
                        <i class="fas fa-th-large" style="margin-right: 5px;"></i>全部商品
                    </div>
                `);

                // 添加分类选项
                categoriesToShow.forEach(category => {
                    const icon = getCategoryIcon(category.name);
                    filtersContainer.append(`
                        <div class="category-filter" data-category="${category.name}">
                            <i class="${icon}" style="margin-right: 5px;"></i>${category.name}
                        </div>
                    `);
                });

                // 重新绑定点击事件
                bindCategoryFilterEvents();
            }

            // 获取分类图标
            function getCategoryIcon(categoryName) {
                const iconMap = {
                    '数码产品': 'fas fa-mobile-alt',
                    '服装鞋帽': 'fas fa-tshirt',
                    '食品饮料': 'fas fa-coffee',
                    '家居用品': 'fas fa-home',
                    '图书文具': 'fas fa-book',
                    '运动户外': 'fas fa-running',
                    '美妆护肤': 'fas fa-heart',
                    '母婴用品': 'fas fa-baby',
                    '汽车用品': 'fas fa-car',
                    '宠物用品': 'fas fa-paw'
                };
                return iconMap[categoryName] || 'fas fa-tag';
            }

            // 绑定分类筛选事件
            function bindCategoryFilterEvents() {
                $('.category-filter').off('click').on('click', function() {
                    const category = $(this).data('category');

                    // 更新激活状态
                    $('.category-filter').removeClass('active');
                    $(this).addClass('active');

                    // 加载对应分类的商品
                    loadProducts(category);
                });
            }

            // 从API加载商品数据
            function loadProducts(category = 'all') {
                const grid = $('#products-grid');

                // 显示加载状态
                grid.html(`
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: rgba(255,255,255,0.7);">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 15px;"></i>
                        <p>正在加载商品...</p>
                    </div>
                `);

                $.ajax({
                    url: 'view/index/get_products.php',
                    method: 'GET',
                    data: { category: category },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            products = response.data;
                            currentCategory = category;
                            renderProducts(products);
                        } else {
                            let errorHtml = `
                                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: rgba(255,255,255,0.7);">
                                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 15px; color: #f59e0b;"></i>
                                    <p>加载商品失败：${response.message}</p>
                            `;

                            if (response.install_url) {
                                errorHtml += `
                                    <div style="margin-top: 20px;">
                                        <a href="${response.install_url}" target="_blank"
                                           style="display: inline-block; background: linear-gradient(45deg, #667eea, #764ba2);
                                                  color: white; padding: 12px 24px; border-radius: 8px;
                                                  text-decoration: none; font-weight: 600;">
                                            <i class="fas fa-download" style="margin-right: 8px;"></i>
                                            点击安装商品表
                                        </a>
                                        <p style="margin-top: 10px; font-size: 0.9rem; opacity: 0.8;">
                                            安装完成后刷新页面即可查看商品
                                        </p>
                                    </div>
                                `;
                            }

                            errorHtml += `</div>`;
                            grid.html(errorHtml);
                        }
                    },
                    error: function() {
                        grid.html(`
                            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: rgba(255,255,255,0.7);">
                                <i class="fas fa-wifi" style="font-size: 2rem; margin-bottom: 15px; color: #ef4444;"></i>
                                <p>网络连接失败，请稍后重试</p>
                                <button class="btn btn-primary mt-2" onclick="loadProducts('${category}')">重新加载</button>
                            </div>
                        `);
                    }
                });
            }

            // 渲染商品列表
            function renderProducts(productsToShow = products) {
                const grid = $('#products-grid');
                grid.empty();

                if (!productsToShow || productsToShow.length === 0) {
                    grid.html(`
                        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: rgba(255,255,255,0.7);">
                            <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
                            <h3 style="margin-bottom: 10px; font-weight: 600;">暂无商品</h3>
                            <p style="margin-bottom: 20px; opacity: 0.8;">当前分类下没有商品，请尝试其他分类或联系管理员添加商品</p>
                            <button class="btn btn-primary" onclick="loadProducts('all')"
                                    style="background: linear-gradient(45deg, #667eea, #764ba2); border: none; padding: 10px 20px; border-radius: 6px; color: white; cursor: pointer;">
                                <i class="fas fa-refresh" style="margin-right: 8px;"></i>查看全部商品
                            </button>
                        </div>
                    `);
                    return;
                }

                productsToShow.forEach(product => {
                    const productCard = `
                        <div class="product-card">
                            <div class="product-header">
                                <div class="product-category">
                                    <i class="fas fa-tag" style="margin-right: 4px;"></i>${product.category}
                                </div>
                                <div class="product-price">¥${product.price.toFixed(2)}</div>
                            </div>
                            <h3 class="product-name">${product.name}</h3>
                            <p class="product-description">${product.description}</p>
                            <a href="${product.buy_link}" target="_blank" class="buy-button">
                                <i class="fas fa-shopping-cart"></i>立即购买
                            </a>
                        </div>
                    `;
                    grid.append(productCard);
                });
            }

            // 当切换到商城标签时加载分类和商品
            $('.nav-tab[data-target="shop"]').click(function() {
                if (categories.length === 0) {
                    loadCategories();
                }
                if (products.length === 0) {
                    loadProducts('all');
                }
            });

            // 复制群号功能 (小版本)
            window.copyGroupNumberSmall = function() {
                const groupNumber = document.getElementById('qq-group-number-small').textContent.trim();

                // 尝试使用现代API复制
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(groupNumber).then(function() {
                        showToast('群号已复制到剪贴板！', 'success');
                    }).catch(function() {
                        fallbackCopy(groupNumber);
                    });
                } else {
                    fallbackCopy(groupNumber);
                }
            };

            // 备用复制方法
            function fallbackCopy(text) {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showToast('群号已复制到剪贴板！', 'success');
                } catch (err) {
                    showToast('复制失败，请手动复制群号', 'error');
                }
                document.body.removeChild(textArea);
            }



            // 简单的提示函数
            function showToast(message, type = 'info') {
                const colors = {
                    success: '#10b981',
                    error: '#ef4444',
                    info: '#3b82f6'
                };

                const toast = document.createElement('div');
                toast.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: ${colors[type]};
                    color: white;
                    padding: 12px 20px;
                    border-radius: 8px;
                    font-size: 0.9rem;
                    font-weight: 500;
                    z-index: 10000;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                    transform: translateX(100%);
                    transition: transform 0.3s ease;
                `;
                toast.textContent = message;
                document.body.appendChild(toast);

                // 显示动画
                setTimeout(() => {
                    toast.style.transform = 'translateX(0)';
                }, 100);

                // 自动隐藏
                setTimeout(() => {
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }

        });
    </script>
</body>
</html>
