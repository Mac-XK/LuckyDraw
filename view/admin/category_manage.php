<?php
// 商品分类管理页面
$codeuse = 0; 
$emailuse = 0;
$directoryPath = '../../';
include("../../core/xiaocore.php");

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../../admin.html');
    exit;
}

// 处理分类操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // 添加分类
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $sortOrder = intval($_POST['sort_order'] ?? 0);
        
        $stmt = $conn->prepare("INSERT INTO product_categories (name, description, sort_order) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $sortOrder);
        
        if ($stmt->execute()) {
            $message = "分类添加成功！";
            $messageType = "success";
        } else {
            $message = "分类添加失败：" . $conn->error;
            $messageType = "error";
        }
        $stmt->close();
    }
    
    if ($action === 'edit') {
        // 编辑分类
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $sortOrder = intval($_POST['sort_order'] ?? 0);
        $status = intval($_POST['status'] ?? 1);
        
        $stmt = $conn->prepare("UPDATE product_categories SET name=?, description=?, sort_order=?, status=? WHERE id=?");
        $stmt->bind_param("ssiii", $name, $description, $sortOrder, $status, $id);
        
        if ($stmt->execute()) {
            $message = "分类更新成功！";
            $messageType = "success";
        } else {
            $message = "分类更新失败：" . $conn->error;
            $messageType = "error";
        }
        $stmt->close();
    }
    
    if ($action === 'delete') {
        // 删除分类
        $id = intval($_POST['id']);
        
        // 检查是否有商品使用此分类
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category = (SELECT name FROM product_categories WHERE id = ?)");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();
        $checkStmt->close();
        
        if ($row['count'] > 0) {
            $message = "无法删除：该分类下还有 {$row['count']} 个商品，请先移动或删除这些商品。";
            $messageType = "error";
        } else {
            $stmt = $conn->prepare("DELETE FROM product_categories WHERE id=?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = "分类删除成功！";
                $messageType = "success";
            } else {
                $message = "分类删除失败：" . $conn->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    }
}

// 获取分类列表
$categories = [];
$result = $conn->query("SELECT * FROM product_categories ORDER BY sort_order DESC, id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分类管理 - 后台管理系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border: 1px solid rgba(0, 0, 0, 0.125); }
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
                    <h2><i class="fas fa-tags me-2"></i>商品分类管理</h2>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus me-2"></i>添加分类
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
                                        <th>分类名称</th>
                                        <th>描述</th>
                                        <th>状态</th>
                                        <th>排序</th>
                                        <th>商品数量</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">暂无分类，点击上方按钮添加第一个分类</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                    <?php
                                    // 获取该分类下的商品数量
                                    $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category = ?");
                                    $countStmt->bind_param("s", $category['name']);
                                    $countStmt->execute();
                                    $countResult = $countStmt->get_result();
                                    $countRow = $countResult->fetch_assoc();
                                    $productCount = $countRow['count'];
                                    $countStmt->close();
                                    ?>
                                    <tr>
                                        <td><?php echo $category['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $category['status'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $category['status'] ? '启用' : '禁用'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $category['sort_order']; ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $productCount; ?> 个商品</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', <?php echo $productCount; ?>)">
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

    <!-- 添加分类模态框 -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>添加分类</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">分类名称 *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">分类描述</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">排序权重</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                            <div class="form-text">数值越大排序越靠前</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">添加分类</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 编辑分类模态框 -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>编辑分类</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editCategoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">分类名称 *</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">分类描述</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">状态</label>
                                    <select name="status" id="edit_status" class="form-select">
                                        <option value="1">启用</option>
                                        <option value="0">禁用</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">排序权重</label>
                                    <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                                </div>
                            </div>
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
        function editCategory(category) {
            document.getElementById('edit_id').value = category.id;
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_description').value = category.description || '';
            document.getElementById('edit_status').value = category.status;
            document.getElementById('edit_sort_order').value = category.sort_order;

            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }

        function deleteCategory(id, name, productCount) {
            let message = `确定要删除分类"${name}"吗？`;
            if (productCount > 0) {
                message += `\n\n注意：该分类下还有 ${productCount} 个商品，删除分类后这些商品将无法正常显示！`;
            }
            message += `\n\n此操作不可恢复！`;

            if (confirm(message)) {
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
