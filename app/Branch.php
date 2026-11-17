
<?php

class Branch
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy danh sách cơ sở
    public function getAll()
    {
        $sql = "SELECT * FROM branches ORDER BY id DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm cơ sở
    public function create($name, $address)
    {
        $sql = "INSERT INTO branches (branch_name, address)
                VALUES (?, ?)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$name, $address]);
    }

    // Lấy cơ sở theo ID
    public function find($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM branches WHERE id = ?"
        );

        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật cơ sở
    public function update($id, $name, $address)
    {
        $sql = "UPDATE branches
                SET branch_name = ?, address = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$name, $address, $id]);
    }

    // Xóa cơ sở
    public function delete($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM branches WHERE id = ?"
        );

        return $stmt->execute([$id]);
    }
}
