
<?php

require_once '../config/config.php';
require_once '../app/Database.php';
require_once '../app/Branch.php';

$db = new Database();
$conn = $db->connect();

$branch = new Branch($conn);

// Thông báo
$message = "";

// Thêm cơ sở
if (isset($_POST['add'])) {
    $name = trim($_POST['branch_name']);
    $address = trim($_POST['address']);

    if ($name !== "" && $address !== "") {
        $branch->create($name, $address);
        header("Location: branches.php?msg=added");
        exit;
    }

    $message = "Vui lòng nhập đầy đủ thông tin!";
}

// Sửa cơ sở
if (isset($_POST['update'])) {
    $branch->update(
        $_POST['id'],
        trim($_POST['branch_name']),
        trim($_POST['address'])
    );

    header("Location: branches.php?msg=updated");
    exit;
}

// Xóa cơ sở
if (isset($_POST['delete'])) {
    $branch->delete($_POST['id']);

    header("Location: branches.php?msg=deleted");
    exit;
}

// Lấy dữ liệu sửa
$edit = null;

if (isset($_GET['edit'])) {
    $edit = $branch->find($_GET['edit']);
}

$branches = $branch->getAll();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

include 'header.php';
?>

<div class="container mt-4">

    <h3>Quản lý cơ sở</h3>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            Thao tác thành công!
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-danger">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <!-- FORM THÊM / SỬA -->
    <div class="card p-3 mb-4">

        <h5><?= $edit ? 'Cập nhật cơ sở' : 'Thêm cơ sở mới' ?></h5>

        <form method="POST">

            <?php if ($edit): ?>
                <input type="hidden" name="id"
                       value="<?= e($edit['id']) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Tên cơ sở</label>
                <input type="text" name="branch_name"
                       class="form-control"
                       value="<?= e($edit['branch_name'] ?? '') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Địa chỉ</label>
                <input type="text" name="address"
                       class="form-control"
                       value="<?= e($edit['address'] ?? '') ?>"
                       required>
            </div>

            <?php if ($edit): ?>
                <button name="update" class="btn btn-warning">
                    Cập nhật
                </button>

                <a href="branches.php" class="btn btn-secondary">
                    Hủy
                </a>
            <?php else: ?>
                <button name="add" class="btn btn-primary">
                    Thêm cơ sở
                </button>
            <?php endif; ?>

        </form>
    </div>

    <!-- DANH SÁCH CƠ SỞ -->
    <div class="card p-3">

        <h5>Danh sách cơ sở</h5>

        <table class="table table-bordered table-hover">

            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tên cơ sở</th>
                    <th>Địa chỉ</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($branches as $b): ?>
                    <tr>
                        <td><?= e($b['id']) ?></td>
                        <td><?= e($b['branch_name']) ?></td>
                        <td><?= e($b['address']) ?></td>

                        <td>
                            <a href="branches.php?edit=<?= e($b['id']) ?>"
                               class="btn btn-warning btn-sm">
                                Sửa
                            </a>

                            <form method="POST" class="d-inline"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa?')">

                                <input type="hidden" name="id"
                                       value="<?= e($b['id']) ?>">

                                <button name="delete"
                                        class="btn btn-danger btn-sm">
                                    Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>

        </table>
    </div>

</div>

<?php include 'footer.php'; ?>
