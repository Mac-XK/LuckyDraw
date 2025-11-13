<?php
// 商品管理页面
$codeuse = 0;
$emailuse = 0;
$directoryPath = '../../';
include("../../core/xiaocore.php");

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../../admin.html');
    exit;
}

// 处理商品操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // 添加商品
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $description = trim($_POST['description']);
        $buyLink = trim($_POST['buy_link']);
        $category = trim($_POST['category']);
        $sortOrder = intval($_POST['sort_order'] ?? 0);

        $stmt = $conn->prepare("INSERT INTO products (name, price, description, buy_link, category, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsssi", $name, $price, $description, $buyLink, $category, $sortOrder);
        
        if ($stmt->execute()) {
            $message = "商品添加成功！";
            $messageType = "success";
        } else {
            $message = "商品添加失败：" . $conn->error;
            $messageType = "error";
        }
        $stmt->close();
    }
    
    if ($action === 'edit') {
        // 编辑商品
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $description = trim($_POST['description']);
        $buyLink = trim($_POST['buy_link']);
        $category = trim($_POST['category']);
        $sortOrder = intval($_POST['sort_order'] ?? 0);
        $status = intval($_POST['status'] ?? 1);

        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=?, buy_link=?, category=?, sort_order=?, status=? WHERE id=?");
        $stmt->bind_param("sdsssiii", $name, $price, $description, $buyLink, $category, $sortOrder, $status, $id);
        
        if ($stmt->execute()) {
            $message = "商品更新成功！";
            $messageType = "success";
        } else {
            $message = "商品更新失败：" . $conn->error;
            $messageType = "error";
        }
        $stmt->close();
    }
    
    if ($action === 'delete') {
        // 删除商品
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "商品删除成功！";
            $messageType = "success";
        } else {
            $message = "商品删除失败：" . $conn->error;
            $messageType = "error";
        }
        $stmt->close();
    }
}

// 获取商品列表
$products = [];
$result = $conn->query("SELECT id, name, price, description, buy_link, category, status, sort_order, created_at, updated_at FROM products ORDER BY sort_order DESC, id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// 获取分类列表 - 从 product_categories 表获取
$categories = [];
$result = $conn->query("SELECT name FROM product_categories WHERE status = 1 ORDER BY sort_order DESC, id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}

// 如果没有分类，添加默认分类提示
if (empty($categories)) {
    $categories = ['请先在分类管理中添加分类'];
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理 - 后台管理系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border: 1px solid rgba(0, 0, 0, 0.125); }
        .product-image { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        .table th { background-color: #f8f9fa; font-weight: 600; }
        .status-badge { padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 500; }
        .status-active { background-color: #d1e7dd; color: #0f5132; }
        .status-inactive { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-shopping-bag me-2"></i>商品管理</h2>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fas fa-plus me-2"></i>添加商品
                        </button>
                        <a href="xiao_main.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>返回首页
                        </a>
                    </div>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>商品名称</th>
                                        <th>价格</th>
                                        <th>分类</th>
                                        <th>状态</th>
                                        <th>排序</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">暂无商品，点击上方按钮添加第一个商品</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                            <br><small class="text-muted"><?php echo mb_substr(htmlspecialchars($product['description']), 0, 50); ?>...</small>
                                        </td>
                                        <td><span class="text-danger fw-bold">¥<?php echo number_format($product['price'], 2); ?></span></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($product['category']); ?></span></td>
                                        <td>
                                            <span class="status-badge <?php echo $product['status'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $product['status'] ? '上架' : '下架'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $product['sort_order']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 添加商品模态框 -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>添加商品</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">商品名称 *</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">价格 *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">¥</span>
                                        <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">分类</label>
                                    <select name="category" class="form-select">
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">排序权重</label>
                                    <input type="number" name="sort_order" class="form-control" value="0">
                                    <div class="form-text">数值越大排序越靠前</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">购买链接 *</label>
                            <input type="url" name="buy_link" class="form-control" placeholder="https://example.com/buy" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">商品简介 *</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">添加商品</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 编辑商品模态框 -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>编辑商品</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editProductForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">商品名称 *</label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">价格 *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">¥</span>
                                        <input type="number" name="price" id="edit_price" class="form-control" step="0.01" min="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">分类</label>
                                    <select name="category" id="edit_category" class="form-select">
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">状态</label>
                                    <select name="status" id="edit_status" class="form-select">
                                        <option value="1">上架</option>
                                        <option value="0">下架</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">排序权重</label>
                                    <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">购买链接 *</label>
                            <input type="url" name="buy_link" id="edit_buy_link" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">商品简介 *</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">保存更改</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_category').value = product.category;
            document.getElementById('edit_status').value = product.status;
            document.getElementById('edit_sort_order').value = product.sort_order;
            document.getElementById('edit_buy_link').value = product.buy_link;
            document.getElementById('edit_description').value = product.description;

            new bootstrap.Modal(document.getElementById('editProductModal')).show();
        }

        function deleteProduct(id, name) {
            if (confirm(`确定要删除商品"${name}"吗？此操作不可恢复！`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
