<?php
// 商品表安装脚本
$codeuse = 0;
$emailuse = 0;
$directoryPath = './';
include("core/xiaocore.php");

echo "<!DOCTYPE html>";
echo "<html lang='zh'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>商品表安装 - 小猫咪抽奖系统</title>";
echo "<style>";
echo "body { font-family: 'Microsoft YaHei', Arial, sans-serif; margin: 0; padding: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }";
echo ".container { max-width: 800px; margin: 0 auto; background: rgba(255,255,255,0.95); border-radius: 15px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }";
echo "h2 { color: #333; text-align: center; margin-bottom: 30px; font-size: 2rem; }";
echo ".status { margin: 15px 0; padding: 12px 20px; border-radius: 8px; font-weight: 500; }";
echo ".success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }";
echo ".warning { background: #fff3cd; color: #664d03; border: 1px solid #ffecb5; }";
echo ".error { background: #f8d7da; color: #721c24; border: 1px solid #f5c2c7; }";
echo ".info { background: #d1ecf1; color: #055160; border: 1px solid #b8daff; }";
echo ".btn { display: inline-block; padding: 12px 24px; background: linear-gradient(45deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 10px 5px; transition: transform 0.2s; }";
echo ".btn:hover { transform: translateY(-2px); text-decoration: none; color: white; }";
echo "ul { margin: 20px 0; }";
echo "li { margin: 8px 0; }";
echo ".icon { margin-right: 8px; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h2><span class='icon'>🛍️</span>商品表安装脚本</h2>";

try {
    // 检查商品表是否已存在
    $result = $conn->query("SHOW TABLES LIKE 'products'");
    if ($result->num_rows > 0) {
        echo "<div class='status warning'><span class='icon'>⚠️</span>商品表已存在，跳过创建。</div>";
    } else {
        // 创建商品表
        $sql = "CREATE TABLE `products` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL COMMENT '商品名称',
          `price` decimal(10,2) NOT NULL COMMENT '商品价格',
          `description` text NOT NULL COMMENT '商品简介',
          `image` varchar(500) DEFAULT NULL COMMENT '商品图片URL',
          `buy_link` varchar(500) NOT NULL COMMENT '购买跳转链接',
          `category` varchar(100) DEFAULT '默认分类' COMMENT '商品分类',
          `status` tinyint(1) DEFAULT 1 COMMENT '状态：1=上架，0=下架',
          `sort_order` int(11) DEFAULT 0 COMMENT '排序权重',
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
          `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
          PRIMARY KEY (`id`),
          KEY `idx_category` (`category`),
          KEY `idx_status` (`status`),
          KEY `idx_sort` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品表'";
        
        if ($conn->query($sql)) {
            echo "<div class='status success'><span class='icon'>✅</span>商品表创建成功！</div>";
        } else {
            throw new Exception("创建商品表失败：" . $conn->error);
        }
    }
    
    // 检查商品分类表是否已存在
    $result = $conn->query("SHOW TABLES LIKE 'product_categories'");
    if ($result->num_rows > 0) {
        echo "<div class='status warning'><span class='icon'>⚠️</span>商品分类表已存在，跳过创建。</div>";
    } else {
        // 创建商品分类表
        $sql = "CREATE TABLE `product_categories` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(100) NOT NULL COMMENT '分类名称',
          `description` text COMMENT '分类描述',
          `sort_order` int(11) DEFAULT 0 COMMENT '排序权重',
          `status` tinyint(1) DEFAULT 1 COMMENT '状态：1=启用，0=禁用',
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uk_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品分类表'";
        
        if ($conn->query($sql)) {
            echo "<div class='status success'><span class='icon'>✅</span>商品分类表创建成功！</div>";
        } else {
            throw new Exception("创建商品分类表失败：" . $conn->error);
        }
    }

    // 检查是否有示例数据
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        echo "<div class='status info'><span class='icon'>📦</span>正在插入示例商品数据...</div>";
        
        // 插入示例商品数据
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'price' => 7999.00,
                'description' => '全新iPhone 15 Pro，搭载A17 Pro芯片，钛金属设计，专业级摄像系统。支持USB-C接口，更强性能，更长续航。',
                'image' => 'https://store.storeimages.cdn-apple.com/8756/as-images.apple.com/is/iphone-15-pro-finish-select-202309-6-1inch-naturaltitanium?wid=300&hei=300&fmt=p-jpg&qlt=80&.v=1692895703814',
                'buy_link' => 'https://www.apple.com.cn/iphone-15-pro/',
                'category' => '数码产品',
                'sort_order' => 100
            ],
            [
                'name' => 'MacBook Air M2',
                'price' => 8999.00,
                'description' => '全新MacBook Air，搭载M2芯片。13.6英寸Liquid Retina显示屏，轻薄便携，续航长达18小时。适合学习、工作和创作。',
                'image' => 'https://store.storeimages.cdn-apple.com/8756/as-images.apple.com/is/macbook-air-midnight-select-20220606?wid=300&hei=300&fmt=jpeg&qlt=90&.v=1653084303665',
                'buy_link' => 'https://www.apple.com.cn/macbook-air-13-and-15-m2/',
                'category' => '数码产品',
                'sort_order' => 90
            ],
            [
                'name' => 'AirPods Pro 2',
                'price' => 1899.00,
                'description' => '第二代AirPods Pro，主动降噪技术升级，空间音频体验，无线充电盒，续航长达30小时。音质清晰，佩戴舒适。',
                'image' => 'https://store.storeimages.cdn-apple.com/8756/as-images.apple.com/is/MQD83?wid=300&hei=300&fmt=jpeg&qlt=95&.v=1660803972361',
                'buy_link' => 'https://www.apple.com.cn/airpods-pro/',
                'category' => '数码产品',
                'sort_order' => 80
            ],
            [
                'name' => 'Nike Air Force 1',
                'price' => 899.00,
                'description' => '经典Nike Air Force 1运动鞋，百搭设计，舒适耐穿。采用优质皮革材质，经典白色配色，适合日常穿搭。',
                'image' => 'https://static.nike.com/a/images/t_PDP_300_v1/f_auto,q_auto:eco/b7d9211c-26e7-431a-ac24-b0540fb3c00f/air-force-1-07-shoes-WrLlWX.png',
                'buy_link' => 'https://www.nike.com/cn/t/air-force-1-07-shoes-WrLlWX',
                'category' => '服装鞋帽',
                'sort_order' => 70
            ],
            [
                'name' => 'Adidas Ultraboost 22',
                'price' => 1299.00,
                'description' => 'Adidas Ultraboost 22跑鞋，Boost中底科技，提供卓越缓震和能量回弹。Primeknit鞋面，透气舒适，适合跑步运动。',
                'image' => 'https://assets.adidas.com/images/h_300,f_auto,q_auto,fl_lossy,c_fill,g_auto/fbaf991a8e8e4bc2a3e9ad7800a8e7a0_9366/Ultraboost_22_Shoes_Black_GZ0127_01_standard.jpg',
                'buy_link' => 'https://www.adidas.com.cn/ultraboost-22-shoes/GZ0127.html',
                'category' => '服装鞋帽',
                'sort_order' => 60
            ],
            [
                'name' => '星巴克咖啡豆',
                'price' => 128.00,
                'description' => '星巴克精选咖啡豆，浓郁香醇，多种口味可选。采用优质阿拉比卡咖啡豆，专业烘焙，带来纯正咖啡体验。',
                'image' => 'https://globalassets.starbucks.com/assets/94fbcc2ab1e24359850fa1870fc988bc.jpg',
                'buy_link' => 'https://www.starbucks.com.cn/',
                'category' => '食品饮料',
                'sort_order' => 50
            ]
        ];
        
        $stmt = $conn->prepare("INSERT INTO products (name, price, description, image, buy_link, category, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($products as $product) {
            $stmt->bind_param("sdssssi", 
                $product['name'], 
                $product['price'], 
                $product['description'], 
                $product['image'], 
                $product['buy_link'], 
                $product['category'], 
                $product['sort_order']
            );
            
            if ($stmt->execute()) {
                echo "<div class='status success'><span class='icon'>✅</span>插入商品：{$product['name']}</div>";
            } else {
                echo "<div class='status error'><span class='icon'>❌</span>插入商品失败：{$product['name']} - " . $conn->error . "</div>";
            }
        }
        
        $stmt->close();
    } else {
        echo "<div class='status warning'><span class='icon'>⚠️</span>商品数据已存在，跳过插入示例数据。</div>";
    }
    
    // 插入默认分类
    $categories = [
        ['name' => '数码产品', 'description' => '手机、电脑、耳机等数码电子产品', 'sort_order' => 100],
        ['name' => '服装鞋帽', 'description' => '服装、鞋子、帽子等穿戴用品', 'sort_order' => 90],
        ['name' => '食品饮料', 'description' => '食品、饮料、零食等消费品', 'sort_order' => 80],
        ['name' => '家居用品', 'description' => '家具、装饰、生活用品等', 'sort_order' => 70],
        ['name' => '图书文具', 'description' => '图书、文具、办公用品等', 'sort_order' => 60],
        ['name' => '运动户外', 'description' => '运动器材、户外用品等', 'sort_order' => 50]
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO product_categories (name, description, sort_order) VALUES (?, ?, ?)");
    
    foreach ($categories as $category) {
        $stmt->bind_param("ssi", $category['name'], $category['description'], $category['sort_order']);
        $stmt->execute();
    }
    
    $stmt->close();
    echo "<div class='status success'><span class='icon'>✅</span>默认分类数据处理完成！</div>";

    echo "<div class='status success' style='margin-top: 30px; text-align: center; font-size: 1.2rem;'>";
    echo "<span class='icon'>🎉</span><strong>安装完成！</strong>";
    echo "</div>";

    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<h3 style='color: #333; margin-bottom: 20px;'>现在您可以：</h3>";
    echo "<a href='admin.html' class='btn'><span class='icon'>🔧</span>进入后台管理</a>";
    echo "<a href='index.php' class='btn'><span class='icon'>🏠</span>查看前台页面</a>";
    echo "<a href='view/admin/product_manage.php' class='btn'><span class='icon'>📦</span>管理商品</a>";
    echo "<div style='margin-top: 20px; color: #666; font-size: 0.9rem;'>";
    echo "💡 提示：管理商品需要先登录后台";
    echo "</div>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='status error'><span class='icon'>❌</span>安装失败：" . $e->getMessage() . "</div>";
}

$conn->close();
echo "</div>";
echo "</body>";
echo "</html>";
