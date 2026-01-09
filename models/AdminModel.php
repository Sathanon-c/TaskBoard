<?php
// models/AdminModel.php

class AdminModel
{
    private $conn;
    private $table = 'admin';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createAdmin($user_id, $first_name, $last_name, $phone, $gender)
    {
        $sql = "INSERT INTO {$this->table} 
            (user_id, first_name, last_name, phone, gender)
            VALUES (:user_id, :first_name, :last_name, :phone, :gender)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':gender', $gender);
        return $stmt->execute();
    }

    public function updateAdmin($user_id, $data)
    {
        $sql = "UPDATE admin SET 
                first_name = :first_name,
                last_name = :last_name,
                phone = :phone,
                gender = :gender
            WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        $data['user_id'] = $user_id;

        return $stmt->execute($data);
    }

    public function updateAdminProfile($user_id, $data)
{
    // 💡 Note: อ้างอิงจากโค้ด EditUser.php, Admin Profile มี:
    // first_name, last_name, phone, gender ที่อยู่ในตาราง admin
    
    $query = "
        UPDATE admin 
        SET 
            first_name = :first_name,
            last_name = :last_name,
            phone = :phone,
            gender = :gender
        WHERE 
            user_id = :user_id";
            
    $stmt = $this->conn->prepare($query);
    
    // 1. Binding Parameters
    
    // Bind ชื่อและนามสกุล
    $stmt->bindParam(':first_name', $data['first_name']);
    $stmt->bindParam(':last_name', $data['last_name']);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':gender', $data['gender']);
    
    // Binding WHERE condition
    $stmt->bindParam(':user_id', $user_id);
    
    return $stmt->execute();
}
}
