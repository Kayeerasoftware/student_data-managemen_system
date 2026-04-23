<?php

class Student
{
    private $conn;
    private $table_name = 'students';

    // Student ID
    public $id;

    // Table fields
    public $first_name;
    public $last_name;
    public $email;
    public $student_number;
    public $registration_number;
    public $phone_number;
    public $photo_path;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAll()
    {
        // Get all students.
        $query = 'SELECT id, first_name, last_name, email, student_number, registration_number, phone_number, photo_path, created_at, updated_at
                  FROM ' . $this->table_name . '
                  ORDER BY created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne()
    {
        // Get one student.
        $query = 'SELECT id, first_name, last_name, email, student_number, registration_number, phone_number, photo_path, created_at, updated_at
                  FROM ' . $this->table_name . '
                  WHERE id = :id
                  LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        $this->first_name          = $row['first_name'];
        $this->last_name           = $row['last_name'];
        $this->email               = $row['email'];
        $this->student_number      = $row['student_number'];
        $this->registration_number = $row['registration_number'];
        $this->phone_number        = $row['phone_number'];
        $this->photo_path          = $row['photo_path'];
        $this->created_at          = $row['created_at'];
        $this->updated_at          = $row['updated_at'];

        return true;
    }

    public function create()
    {
        // Insert a new student.
        $query = 'INSERT INTO ' . $this->table_name . '
                  SET first_name = :first_name,
                      last_name = :last_name,
                      email = :email,
                      student_number = :student_number,
                      registration_number = :registration_number,
                      phone_number = :phone_number,
                      photo_path = :photo_path';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':first_name', $this->first_name);
        $stmt->bindValue(':last_name', $this->last_name);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':student_number', $this->student_number);
        $stmt->bindValue(':registration_number', $this->registration_number);
        $stmt->bindValue(':phone_number', $this->phone_number);
        $stmt->bindValue(':photo_path', $this->photo_path);
        return $stmt->execute();
    }

    public function update()
    {
        // Update a student.
        $query = 'UPDATE ' . $this->table_name . '
                  SET first_name = :first_name,
                      last_name = :last_name,
                      email = :email,
                      student_number = :student_number,
                      registration_number = :registration_number,
                      phone_number = :phone_number,
                      photo_path = :photo_path
                  WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':first_name', $this->first_name);
        $stmt->bindValue(':last_name', $this->last_name);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':student_number', $this->student_number);
        $stmt->bindValue(':registration_number', $this->registration_number);
        $stmt->bindValue(':phone_number', $this->phone_number);
        $stmt->bindValue(':photo_path', $this->photo_path);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete()
    {
        // Delete a student.
        $query = 'DELETE FROM ' . $this->table_name . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function duplicateExists($email, $student_number, $registration_number, $exclude_id = null)
    {
        // Check if the email or numbers already exist.
        $query = 'SELECT id FROM ' . $this->table_name . '
                  WHERE (email = :email
                     OR student_number = :student_number
                     OR registration_number = :registration_number)';

        if ($exclude_id !== null) {
            $query .= ' AND id <> :exclude_id';
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':student_number', $student_number);
        $stmt->bindValue(':registration_number', $registration_number);

        if ($exclude_id !== null) {
            $stmt->bindValue(':exclude_id', $exclude_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
