
<?php

class Schedule
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy danh sách lịch dạy
    public function getAll()
    {
        $sql = "SELECT s.*, t.full_name, b.branch_name
                FROM schedules s
                JOIN teachers t ON s.teacher_id = t.id
                JOIN branches b ON s.branch_id = b.id
                ORDER BY s.day_of_week, s.shift_or_periods";

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy lịch theo ID
    public function find($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM schedules WHERE id = ?"
        );

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Thêm lịch
    public function create($data)
    {
        $sql = "INSERT INTO schedules
                (teacher_id, branch_id, subject_name,
                 room, day_of_week, shift_or_periods)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $data['teacher_id'],
            $data['branch_id'],
            $data['subject_name'],
            $data['room'],
            $data['day_of_week'],
            $data['shift_or_periods']
        ]);
    }

    // Cập nhật lịch
    public function update($id, $data)
    {
        $sql = "UPDATE schedules SET
                teacher_id = ?,
                branch_id = ?,
                subject_name = ?,
                room = ?,
                day_of_week = ?,
                shift_or_periods = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $data['teacher_id'],
            $data['branch_id'],
            $data['subject_name'],
            $data['room'],
            $data['day_of_week'],
            $data['shift_or_periods'],
            $id
        ]);
    }

    // Xóa lịch
    public function delete($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM schedules WHERE id = ?"
        );

        return $stmt->execute([$id]);
    }

    // Kiểm tra trùng lịch
    public function checkConflict(
        $teacherId,
        $branchId,
        $room,
        $day,
        $periods,
        $excludeId = null
    ) {
        preg_match('/^(\d+)-(\d+)$/', $periods, $match);

        $start = (int)$match[1];
        $end = (int)$match[2];

        $sql = "SELECT s.*, t.full_name, b.branch_name
                FROM schedules s
                JOIN teachers t ON s.teacher_id = t.id
                JOIN branches b ON s.branch_id = b.id
                WHERE s.day_of_week = ?
                AND (
                    s.teacher_id = ?
                    OR (s.branch_id = ? AND s.room = ?)
                )";

        $params = [$day, $teacherId, $branchId, $room];

        if ($excludeId !== null) {
            $sql .= " AND s.id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        $conflicts = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {

            if (!preg_match(
                '/^(\d+)-(\d+)$/',
                $item['shift_or_periods'],
                $old
            )) {
                continue;
            }

            $oldStart = (int)$old[1];
            $oldEnd = (int)$old[2];

            // Kiểm tra hai khoảng tiết có giao nhau không
            if ($start <= $oldEnd && $end >= $oldStart) {

                if ($item['teacher_id'] == $teacherId) {
                    $conflicts[] =
                        "Giảng viên đã có lịch: " . $item['full_name'];
                }

                if (
                    $item['branch_id'] == $branchId &&
                    $item['room'] == $room
                ) {
                    $conflicts[] =
                        "Phòng " . $room . " đã được sử dụng tại cơ sở này";
                }
            }
        }

        return array_unique($conflicts);
    }
}
