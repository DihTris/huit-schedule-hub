
<?php
require_once '../config/config.php';
require_once '../app/Database.php';

$db = new Database();
$conn = $db->connect();

$branches = $conn->query(
    "SELECT COUNT(*) FROM branches"
)->fetchColumn();

$teachers = $conn->query(
    "SELECT COUNT(*) FROM teachers"
)->fetchColumn();

include 'header.php';
?>

<div class="container mt-5">

    <h2>Hệ thống quản lý lịch giảng dạy</h2>

    <p class="text-secondary">
        HUIT Schedule Hub - Quản lý phân lịch đa cơ sở
    </p>

    <div class="row mt-4">

        <div class="col-md-6 mb-3">
            <div class="card p-4 shadow-sm">
                <h5>Tổng số cơ sở</h5>
                <h2 class="text-primary">
                    <?= $branches ?>
                </h2>
                <a href="branches.php" class="btn btn-primary">
                    Quản lý cơ sở
                </a>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card p-4 shadow-sm">
                <h5>Tổng số giảng viên</h5>
                <h2 class="text-success">
                    <?= $teachers ?>
                </h2>
                <a href="teachers.php" class="btn btn-success">
                    Quản lý giảng viên
                </a>
            </div>
        </div>

    </div>

</div>

<?php include 'footer.php'; ?>
