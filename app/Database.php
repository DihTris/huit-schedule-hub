
<?php

class Database
{
    private $conn;

    public function connect()
    {
        try {
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $dbname = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

            $this->conn = new PDO($dsn, $user, $pass);

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            return $this->conn;

        } catch (PDOException $e) {
            die("Lỗi kết nối Database: " . $e->getMessage());
        }
    }
}
