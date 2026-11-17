
<?php

require_once '../config/config.php';
require_once '../app/Database.php';
require_once '../app/Schedule.php';

$db = new Database();
$conn = $db->connect();

$schedule = new Schedule($conn);

$message = "";
$error = "";

// Lấy danh sách giảng viên và cơ sở
$teachers = $conn->query(
    "SELECT * FROM teachers ORDER BY full_name"
)->fetchAll(PDO::FETCH_ASSOC);

$branches = $conn->query(
    "SELECT * FROM branches ORDER BY branch_name"
)->fetchAll(PDO::FETCH_ASSOC);

// Thêm hoặc cập nhật lịch
if (isset($_POST['save'])) {

    $data = [
        'teacher_id' => (int)$_POST['teacher_id'],
        'branch_id' => (int)$_POST['branch_id'],
        'subject_name' => trim($_POST['subject_name']),
        'room' => trim($_POST['room']),
        'day_of_week' => (int)$_POST['day_of_week'],
        'shift_or_periods' => trim($_POST['shift_or_periods'])
    ];

    // Kiểm tra dữ liệu đầu vào
    if (
        $data['teacher_id'] <= 0 ||
        $data['branch_id'] <= 0 ||
        $data['subject_name'] === '' ||
        $data['room'] === '' ||
        $data['day_of_week'] < 2 ||
        $data['day_of_week'] > 8
    ) {
        $error = "Vui lòng nhập đầy đủ thông tin hợp lệ!";
    }
    elseif (
        !preg_match('/^([1-9]|1[0-2])-([1-9]|1[0-2])$/',
                    $data['shift_or_periods'], $match)
        || (int)$match[1] > (int)$match[2]
    ) {
        $error = "Tiết học phải có dạng 1-3, 4-6 và từ tiết 1 đến 12!";
    }
    else {

        // Kiểm tra trùng lịch
        $editId = !empty($_POST['id'])
            ? (int)$_POST['id']
            : null;

        $conflicts = $schedule->checkConflict(
            $data['teacher_id'],
            $data['branch_id'],
            $data['room'],
            $data['day_of_week'],
            $data['shift_or_periods'],
            $editId
        );

        if (!empty($conflicts)) {

            $error = implode(" | ", $conflicts);

        } else {

            if ($editId) {
                $schedule->update($editId, $data);
            } else {
                $schedule->create($data);
            }

            header("Location: schedules.php?msg=success");
            exit;
        }
    }
}

// Xóa lịch
if (isset($_POST['delete'])) {

    $schedule->delete((int)$_POST['id']);

    header("Location: schedules.php?msg=deleted");
    exit;
}

// Lấy dữ liệu chỉnh sửa
$edit = null;

if (isset($_GET['edit'])) {
    $edit = $schedule->find((int)$_GET['edit']);
}

// Lấy danh sách lịch
$schedules = $schedule->getAll();

// Hàm bảo vệ dữ liệu hiển thị
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

include 'header.php';
?>

<div class="container mt-4">

    <h3 class="mb-4">Quản lý lịch giảng dạy</h3>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            Thao tác thành công!
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- FORM LỊCH DẠY -->
    <div class="card shadow-sm p-3 mb-4">

        <h5>
            <?= $edit ? 'Cập nhật lịch dạy' : 'Thêm lịch dạy mới' ?>
        </h5>

        <form method="POST">

            <?php if ($edit): ?>
                <input type="hidden" name="id"
                       value="<?= e($edit['id']) ?>">
            <?php endif; ?>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Giảng viên</label>

                    <select name="teacher_id" class="form-select" required>
                        <option value="">-- Chọn giảng viên --</option>

                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= e($t['id']) ?>"
                                <?= ($edit && $edit['teacher_id'] == $t['id'])
                                    ? 'selected' : '' ?>>

                                <?= e($t['full_name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Cơ sở</label>

                    <select name="branch_id" class="form-select" required>
                        <option value="">-- Chọn cơ sở --</option>

                        <?php foreach ($branches as $b): ?>
                            <option value="<?= e($b['id']) ?>"
                                <?= ($edit && $edit['branch_id'] == $b['id'])
                                    ? 'selected' : '' ?>>

                                <?= e($b['branch_name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên học phần</label>

                    <input type="text" name="subject_name"
                           class="form-control"
                           value="<?= e($edit['subject_name'] ?? '') ?>"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Phòng học</label>

                    <input type="text" name="room"
                           class="form-control"
                           value="<?= e($edit['room'] ?? '') ?>"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Thứ</label>

                    <select name="day_of_week" class="form-select" required>

                        <?php for ($i = 2; $i <= 8; $i++): ?>

                            <option value="<?= $i ?>"
                                <?= ($edit && $edit['day_of_week'] == $i)
                                    ? 'selected' : '' ?>>

                                <?= $i == 8 ? 'Chủ nhật' : 'Thứ ' . $i ?>

                            </option>

                        <?php endfor; ?>

                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tiết học</label>

                    <select name="shift_or_periods"
                            class="form-select" required>

                        <option value="">-- Chọn tiết học --</option>

                        <?php
                        $periods = ['1-3', '4-6', '7-9', '10-12'];
                        ?>

                        <?php foreach ($periods as $p): ?>

                            <option value="<?= $p ?>"
                                <?= ($edit && $edit['shift_or_periods'] == $p)
                                    ? 'selected' : '' ?>>

                                Tiết <?= $p ?>

                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

            </div>

            <?php if ($edit): ?>

                <button type="submit" name="save"
                        class="btn btn-warning">
                    Cập nhật lịch
                </button>

                <a href="schedules.php" class="btn btn-secondary">
                    Hủy
                </a>

            <?php else: ?>

                <button type="submit" name="save"
                        class="btn btn-primary">
                    Thêm lịch dạy
                </button>

            <?php endif; ?>

        </form>

    </div>

    <!-- DANH SÁCH LỊCH DẠY -->
    <div class="card shadow-sm p-3">

        <h5>Danh sách lịch giảng dạy</h5>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Giảng viên</th>
                        <th>Học phần</th>
                        <th>Cơ sở</th>
                        <th>Phòng</th>
                        <th>Thứ</th>
                        <th>Tiết</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($schedules as $s): ?>

                        <tr>

                            <td><?= e($s['id']) ?></td>

                            <td><?= e($s['full_name']) ?></td>

                            <td><?= e($s['subject_name']) ?></td>

                            <td><?= e($s['branch_name']) ?></td>

                            <td><?= e($s['room']) ?></td>

                            <td>
                                <?= $s['day_of_week'] == 8
                                    ? 'Chủ nhật'
                                    : 'Thứ ' . e($s['day_of_week']) ?>
                            </td>

                            <td><?= e($s['shift_or_periods']) ?></td>

                            <td>

                                <a href="schedules.php?edit=<?= e($s['id']) ?>"
                                   class="btn btn-warning btn-sm">
                                    Sửa
                                </a>

                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa lịch này?')">

                                    <input type="hidden" name="id"
                                           value="<?= e($s['id']) ?>">

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

</div>

<?php include 'footer.php'; ?>
