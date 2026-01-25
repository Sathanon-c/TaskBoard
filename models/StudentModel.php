<?php
// models/StudentModel.php

class StudentModel
{
    private $conn;
    private $table = 'student';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createStudent($user_id, $first_name, $last_name, $student_code, $major, $year, $phone, $gender, $class_id)
    {
        $sql = "INSERT INTO {$this->table} 
            (user_id, first_name, last_name, student_code, major, year, phone, gender, class_id) 
            VALUES (:user_id, :first_name, :last_name, :student_code, :major, :year, :phone, :gender, :class_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':student_code', $student_code);
        $stmt->bindParam(':major', $major);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':class_id', $class_id);
        return $stmt->execute();
    }

    public function updateStudent($user_id, $data)
    {
        $sql = "UPDATE student SET 
            first_name = :first_name,
            last_name = :last_name,
            student_code = :student_code,
            major = :major,
            year = :year,
            phone = :phone,
            gender = :gender,
            class_id = :class_id
            WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $data['user_id'] = $user_id;
        return $stmt->execute($data);
    }

    public function getStudentsNotInCourse($course_id)
    {
        $sql = "
        SELECT s.*
        FROM student s
        WHERE s.student_id NOT IN (
            SELECT student_id 
            FROM enrollment 
            WHERE course_id = :course_id
        )
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ใน models/StudentModel.php
    public function updateStudentProfile($user_id, $data)
    {
        // 1. สร้าง Query SQL (กำหนดฟิลด์ทั้งหมดอย่างชัดเจน)
        $query = "
        UPDATE student 
        SET 
            first_name = :first_name,
            last_name = :last_name,
            phone = :phone,
            gender = :gender,
            student_code = :student_code,
            major = :major,
            year = :year,
            class_id = :class_id
        WHERE 
            user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':student_code', $data['student_code']);
        $stmt->bindParam(':major', $data['major']);
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':class_id', $data['class_id']);
        $stmt->bindParam(':user_id', $user_id);

        // 3. Execute
        return $stmt->execute();
    }


    // ใน models/StudentModel.php
    public function getStudentProfileByUserId($user_id)
    {
        // สมมติว่าตาราง 'student' มีคอลัมน์ first_name, last_name, student_code, major, year, phone, gender
        // และมีคอลัมน์ user_id ที่เชื่อมโยงกับตาราง 'user'
        $query = "
        SELECT 
            s.student_id, 
            s.first_name,
            s.last_name, 
            s.student_code,
            s.major, 
            s.year, 
            s.phone, 
            s.gender,
            s.class_id,
            u.email             -- ดึง email จากตาราง user
        FROM 
            student s
        JOIN 
            user u ON s.user_id = u.user_id 
        WHERE 
            s.user_id = :user_id
        LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getStudentIdByUserId($user_id)
    {
        $query = "SELECT student_id FROM " . $this->table . " WHERE user_id = :user_id LIMIT 1";

        $stmt = $this->conn->prepare($query);

        // Bind the user_id parameter
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // คืนค่า student_id
            return (int)$row['student_id'];
        }

        return false;
    }

    public function getStudentById($student_id)
{
    $query = "
    SELECT 
        s.student_id, 
        s.first_name,
        s.last_name, 
        s.student_code,
        s.major, 
        s.year, 
        s.class_id,
        u.email 
    FROM 
        student s
    JOIN 
        user u ON s.user_id = u.user_id 
    WHERE 
        s.student_id = :student_id
    LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}
