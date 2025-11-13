<?php
// 测试数据库连接和抽奖记录
require_once 'core/config.php';

echo "<h2>数据库连接测试</h2>";

// 检查配置
echo "<h3>1. 数据库配置检查</h3>";
echo "主机: " . ($host ?: '❌ 未配置') . "<br>";
echo "数据库: " . ($db ?: '❌ 未配置') . "<br>";
echo "用户名: " . ($user ?: '❌ 未配置') . "<br>";
echo "密码: " . ($pass ? '✅ 已配置' : '❌ 未配置') . "<br>";

if (empty($host) || empty($db) || empty($user)) {
    echo "<div style='color: red; font-weight: bold; margin: 20px 0;'>";
    echo "❌ 数据库配置不完整！请检查 core/config.php 文件<br>";
    echo "这就是为什么抽奖记录不显示的原因。";
    echo "</div>";
    
    echo "<h3>解决方案：</h3>";
    echo "<p>请编辑 <code>core/config.php</code> 文件，填入正确的数据库连接信息：</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo "&lt;?php\n";
    echo "\$host = 'localhost';        // 数据库主机\n";
    echo "\$db = 'your_database_name'; // 数据库名称\n";
    echo "\$user = 'your_username';    // 数据库用户名\n";
    echo "\$pass = 'your_password';    // 数据库密码\n";
    echo "\$charset = 'utf8mb4';\n";
    echo "?&gt;";
    echo "</pre>";
    exit;
}

// 尝试连接数据库
echo "<h3>2. 数据库连接测试</h3>";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "❌ 连接失败: " . $conn->connect_error . "<br>";
    echo "<div style='color: red; margin: 10px 0;'>";
    echo "请检查数据库配置信息是否正确。";
    echo "</div>";
    exit;
} else {
    echo "✅ 数据库连接成功<br>";
}

// 检查表是否存在
echo "<h3>3. 数据表检查</h3>";
$tables = ['lottery_logs', 'prizes', 'admins', 'lottery_limits'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ 表 $table 存在<br>";
    } else {
        echo "❌ 表 $table 不存在<br>";
    }
}

// 检查抽奖记录
echo "<h3>4. 抽奖记录检查</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM lottery_logs");
if ($result) {
    $row = $result->fetch_assoc();
    echo "📊 抽奖记录总数: " . $row['count'] . "<br>";
    
    if ($row['count'] > 0) {
        echo "<h4>最近5条记录：</h4>";
        $recent = $conn->query("SELECT account, date, message, prize_id FROM lottery_logs ORDER BY id DESC LIMIT 5");
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>邮箱</th><th>日期</th><th>留言</th><th>奖品ID</th></tr>";
        while ($record = $recent->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($record['account']) . "</td>";
            echo "<td>" . $record['date'] . "</td>";
            echo "<td>" . htmlspecialchars($record['message']) . "</td>";
            echo "<td>" . ($record['prize_id'] ? $record['prize_id'] : '未中奖') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "📝 暂无抽奖记录<br>";
        echo "<div style='color: orange; margin: 10px 0;'>";
        echo "如果您已经进行过抽奖但没有记录，可能是：<br>";
        echo "1. 抽奖时数据库连接失败<br>";
        echo "2. 抽奖页面有错误<br>";
        echo "3. 浏览器缓存问题";
        echo "</div>";
    }
} else {
    echo "❌ 查询失败: " . $conn->error . "<br>";
}

// 检查奖品设置
echo "<h3>5. 奖品设置检查</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM prizes");
if ($result) {
    $row = $result->fetch_assoc();
    echo "🎁 奖品总数: " . $row['count'] . "<br>";
    
    if ($row['count'] > 0) {
        $prizes = $conn->query("SELECT name, probability FROM prizes ORDER BY id");
        echo "<h4>奖品列表：</h4>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>奖品名称</th><th>中奖概率</th></tr>";
        while ($prize = $prizes->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($prize['name']) . "</td>";
            echo "<td>" . $prize['probability'] . "%</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ 没有设置奖品<br>";
        echo "<div style='color: red; margin: 10px 0;'>";
        echo "请在后台管理中添加奖品，否则无法进行抽奖。";
        echo "</div>";
    }
}

$conn->close();

echo "<h3>6. 建议</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "如果数据库连接正常但仍然没有抽奖记录，请：<br>";
echo "1. 清除浏览器缓存<br>";
echo "2. 检查浏览器控制台是否有JavaScript错误<br>";
echo "3. 确认抽奖页面的网络请求是否成功<br>";
echo "4. 检查服务器错误日志";
echo "</div>";
?>
