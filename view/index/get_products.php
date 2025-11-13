<?php
// 获取商品数据的API接口
header('Content-Type: application/json; charset=utf-8');

// 引入数据库连接
$codeuse = 0;
$emailuse = 0;
$directoryPath = '../../';
include($directoryPath . "core/xiaocore.php");

try {
    // 检查商品表是否存在
    $result = $conn->query("SHOW TABLES LIKE 'products'");
    if ($result->num_rows == 0) {
        echo json_encode([
            'success' => false,
            'message' => '商品表不存在，请先运行 install_products.php 安装商品表',
            'install_url' => '../../install_products.php'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 获取请求参数
    $category = $_GET['category'] ?? 'all';
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);

    // 构建SQL查询
    $sql = "SELECT id, name, price, description, buy_link, category, sort_order FROM products WHERE status = 1";
    $params = [];
    $types = "";
    
    if ($category !== 'all' && !empty($category)) {
        $sql .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    $sql .= " ORDER BY sort_order DESC, id DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= "i";
        
        if ($offset > 0) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
            $types .= "i";
        }
    }
    
    // 执行查询
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // 格式化价格
        $row['price'] = floatval($row['price']);
        $row['sort_order'] = intval($row['sort_order']);

        // 限制描述长度
        if (strlen($row['description']) > 200) {
            $row['description'] = mb_substr($row['description'], 0, 200) . '...';
        }

        $products[] = $row;
    }
    
    $stmt->close();
    
    // 获取分类统计
    $categoryStats = [];
    $categoryResult = $conn->query("SELECT category, COUNT(*) as count FROM products WHERE status = 1 GROUP BY category");
    if ($categoryResult) {
        while ($row = $categoryResult->fetch_assoc()) {
            $categoryStats[$row['category']] = intval($row['count']);
        }
    }
    
    // 返回成功响应
    echo json_encode([
        'success' => true,
        'data' => $products,
        'total' => count($products),
        'category_stats' => $categoryStats,
        'current_category' => $category
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // 返回错误响应
    echo json_encode([
        'success' => false,
        'message' => '获取商品数据失败：' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
