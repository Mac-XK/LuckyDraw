<?php
header('Content-Type: application/json; charset=utf-8');
$codeuse = 0; 
$emailuse = 0;
$directoryPath = '../../';
include($directoryPath . "core/xiaocore.php");

try {
    // 检查分类表是否存在
    $result = $conn->query("SHOW TABLES LIKE 'product_categories'");
    if ($result->num_rows == 0) {
        echo json_encode([
            'success' => false,
            'message' => '分类表不存在，请先运行 install_products.php 安装分类表',
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 获取启用的分类
    $sql = "SELECT name, description FROM product_categories WHERE status = 1 ORDER BY sort_order DESC, id ASC";
    $result = $conn->query($sql);
    
    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'name' => $row['name'],
                'description' => $row['description']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'message' => '获取分类成功',
        'data' => $categories
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '获取分类失败：' . $e->getMessage(),
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>
