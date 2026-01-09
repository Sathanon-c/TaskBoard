<?php
// models/TeacherModel.php

class TeacherModel
{
    private $conn;
    private $table = 'teacher';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createTeacher($user_id, $first_name, $last_name, $phone, $gender, $department)
    {
        $sql = "INSERT INTO {$this->table} 
            (user_id, first_name, last_name, phone, gender, department)
            VALUES (:user_id, :first_name, :last_name, :phone, :gender, :department)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':department', $department);
        return $stmt->execute();
    }

    public function updateTeacher($user_id, $data)
    {
        $sql = "UPDATE teacher SET 
                first_name = :first_name,
                last_name = :last_name,
                department = :department,
                phone = :phone,
                gender = :gender
            WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        $data['user_id'] = $user_id;

        return $stmt->execute($data);
    }

    // ใน models/TeacherModel.php

public function updateTeacherProfile($user_id, $data)
{
    // ✅ เพิ่ม first_name และ last_name เข้ามาใน SET
    $query = "
        UPDATE teacher 
        SET 
            first_name = :first_name,
            last_name = :last_name,
            phone = :phone,
            gender = :gender,
            department = :department
        WHERE 
            user_id = :user_id";
            
    $stmt = $this->conn->prepare($query);
    
    // 1. Binding Parameters (ต้องเรียงลำดับให้ครบตาม Query)
    
    // ✅ Bind ชื่อและนามสกุล
    $stmt->bindParam(':first_name', $data['first_name']);
    $stmt->bindParam(':last_name', $data['last_name']);
    
    // Bind ข้อมูล Profile อื่นๆ
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':gender', $data['gender']);
    $stmt->bindParam(':department', $data['department']);
    
    // Binding WHERE condition
    $stmt->bindParam(':user_id', $user_id);
    
    return $stmt->execute();
}
// ใน models/TeacherModel.php

/**
 * แปลง User ID (จาก Session) เป็น Teacher ID (จากตาราง Teacher)
 */
public function getTeacherIdByUserId($user_id)
{
    // สมมติว่าตาราง 'teacher' มีคอลัมน์ 'user_id' และ 'teacher_id'
    $query = "SELECT teacher_id FROM teacher WHERE user_id = :user_id LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // คืนค่า teacher_id (หรือ null ถ้าไม่พบ)
    return $result ? $result['teacher_id'] : null;
}
}
