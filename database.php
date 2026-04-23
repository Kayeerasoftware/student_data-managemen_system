<?php

class Database
{
    private $host     = 'localhost';
    private $db_name  = 'student_data_managemen_system_db';
    private $username = 'root';
    private $password = '';
    private $conn     = null;

    public function getConnection()
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4';
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }

        return $this->conn;
    }
}
