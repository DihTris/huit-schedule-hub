
<?php

class Teacher
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy danh sách giảng viên
    public function getAll()
    {
        $sql = "SELECT * FROM teachers ORDER BY id DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm giảng viên
    public function create($name, $email, $phone, $expertise)
    {
        $sql = "INSERT INTO teachers
                (full_name, email, phone, expertise)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $name, $email, $phone, $expertise
        ]);
    }

    // Lấy giảng viên theo ID
    public function find($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM teachers WHERE id = ?"
        );

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật giảng viên
    public function update($id, $name, $email, $phone, $expertise)
    {
        $sql = "UPDATE teachers
                SET full_name = ?, email = ?,
                    phone = ?, expertise = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $name, $email, $phone, $expertise, $id
        ]);
    }

    // Xóa giảng viên
    public function delete($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM teachers WHERE id = ?"
        );

        return $stmt->execute([$id]);
    }
}
