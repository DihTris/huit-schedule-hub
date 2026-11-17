
<?php

require_once '../config/config.php';
require_once '../app/Database.php';
require_once '../app/Teacher.php';

$db = new Database();
$conn = $db->connect();

$teacher = new Teacher($conn);

$message = "";

// Thêm giảng viên
if (isset($_POST['add'])) {
    try {
        $teacher->create(
            trim($_POST['full_name']),
            trim($_POST['email']),
            trim($_POST['phone']),
            trim($_POST['expertise'])
        );

        header("Location: teachers.php?msg=added");
        exit;

    } catch (PDOException $e) {
        $message = "Email đã tồn tại hoặc dữ liệu không hợp lệ!";
    }
}

// Cập nhật giảng viên
if (isset($_POST['update'])) {
    try {
        $teacher->update(
            $_POST['id'],
            trim($_POST['full_name']),
            trim($_POST['email']),
            trim($_POST['phone']),
            trim($_POST['expertise'])
        );

        header("Location: teachers.php?msg=updated");
        exit;

    } catch (PDOException $e) {
        $message = "Email đã tồn tại hoặc dữ liệu không hợp lệ!";
    }
}

// Xóa giảng viên
if (isset($_POST['delete'])) {
    try {
        $teacher->delete($_POST['id']);

        header("Location: teachers.php?msg=deleted");
        exit;

    } catch (PDOException $e) {
        $message = "Không thể xóa giảng viên đang có lịch dạy hoặc tài khoản!";
    }
}

// Lấy dữ liệu cần sửa
$edit = null;

if (isset($_GET['edit'])) {
    $edit = $teacher->find($_GET['edit']);
}

$teachers = $teacher->getAll();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

include 'header.php';
?>

<div class="container mt-4">

    <h3>Quản lý giảng viên</h3>

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

        <h5><?= $edit ? 'Cập nhật giảng viên' : 'Thêm giảng viên mới' ?></h5>

        <form method="POST">

            <?php if ($edit): ?>
                <input type="hidden" name="id"
                       value="<?= e($edit['id']) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Họ và tên</label>
                <input type="text" name="full_name"
                       class="form-control"
                       value="<?= e($edit['full_name'] ?? '') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email"
                       class="form-control"
                       value="<?= e($edit['email'] ?? '') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="phone"
                       class="form-control"
                       value="<?= e($edit['phone'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Chuyên môn</label>
                <input type="text" name="expertise"
                       class="form-control"
                       value="<?= e($edit['expertise'] ?? '') ?>"
                       required>
            </div>

            <?php if ($edit): ?>
                <button name="update" class="btn btn-warning">
                    Cập nhật
                </button>

                <a href="teachers.php" class="btn btn-secondary">
                    Hủy
                </a>
            <?php else: ?>
                <button name="add" class="btn btn-primary">
                    Thêm giảng viên
                </button>
            <?php endif; ?>

        </form>
    </div>

    <!-- DANH SÁCH GIẢNG VIÊN -->
    <div class="card p-3">

        <h5>Danh sách giảng viên</h5>

        <table class="table table-bordered table-hover">

            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Chuyên môn</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($teachers as $t): ?>
                    <tr>
                        <td><?= e($t['id']) ?></td>
                        <td><?= e($t['full_name']) ?></td>
                        <td><?= e($t['email']) ?></td>
                        <td><?= e($t['phone']) ?></td>
                        <td><?= e($t['expertise']) ?></td>

                        <td>
                            <a href="teachers.php?edit=<?= e($t['id']) ?>"
                               class="btn btn-warning btn-sm">
                                Sửa
                            </a>

                            <form method="POST" class="d-inline"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa?')">

                                <input type="hidden" name="id"
                                       value="<?= e($t['id']) ?>">

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
