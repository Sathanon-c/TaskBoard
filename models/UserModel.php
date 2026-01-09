<?php
class UserModel
{
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function getUsers($role = "", $search = ""){
        $sql = "SELECT u.user_id, u.email, u.role, u.active, u.created_at,
            COALESCE(s.first_name, t.first_name, a.first_name) AS first_name,
            COALESCE(s.last_name, t.last_name, a.last_name) AS last_name
            FROM user u
            LEFT JOIN student s ON u.user_id = s.user_id
            LEFT JOIN teacher t ON u.user_id = t.user_id
            LEFT JOIN admin a ON u.user_id = a.user_id
            WHERE 1 ";

        $params = [];

        if (!empty($role)) {
            $sql .= " AND u.role = :role ";
            $params[':role'] = $role;
        }

        if (!empty($search)) {
            $sql .= " AND (
                        u.email LIKE :search OR
                        s.first_name LIKE :search OR
                        s.last_name LIKE :search OR
                        t.first_name LIKE :search OR
                        t.last_name LIKE :search OR
                        a.first_name LIKE :search OR
                        a.last_name LIKE :search
                    )";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($user_id){
        $sql = "SELECT u.user_id, u.email, u.role, u.active, u.created_at,
                COALESCE(s.first_name, t.first_name, a.first_name) AS first_name,
                COALESCE(s.last_name, t.last_name, a.last_name) AS last_name,
                s.student_code, s.major, s.year,s.class_id, s.phone AS s_phone, s.gender AS s_gender,
                t.phone AS t_phone, t.gender AS t_gender, t.department,
                a.phone AS a_phone, a.gender AS a_gender
                FROM user u
                LEFT JOIN student s ON u.user_id = s.user_id
                LEFT JOIN teacher t ON u.user_id = t.user_id
                LEFT JOIN admin a ON u.user_id = a.user_id
                WHERE u.user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($email, $password, $role){
        $sql = "INSERT INTO user (email, password, role) VALUES (:email, :password, :role)";
        $stmt = $this->conn->prepare($sql);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function updateUser($user_id, $data)
    {
        $fields = [];
        $params = ['user_id' => $user_id];

        if (!empty($data['email'])) {
            $fields[] = "email = :email";
            $params['email'] = $data['email'];
        }

        if (isset($data['active'])) {
            $fields[] = "active = :active";
            $params['active'] = $data['active'];
        }

        if (empty($fields)) return false;

        $sql = "UPDATE user SET " . implode(", ", $fields) . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    // ใน models/UserModel.php

public function countAllUsers()
{
    $query = "SELECT COUNT(*) FROM user";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    
    // ดึงค่าแรกของแถวแรก (ซึ่งคือจำนวนนับ)
    return $stmt->fetchColumn(); 
}

// ใน models/UserModel.php

public function countActiveUsers()
{
    $query = "SELECT COUNT(*) FROM user WHERE active = 1";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchColumn(); 
}

// ใน models/UserModel.php

public function countInactiveUsers()
{
    $query = "SELECT COUNT(*) FROM user WHERE active = 0";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchColumn(); 
}
// ใน models/UserModel.php

// ใน models/UserModel.php

public function getRecentInactiveUsers($limit = 5)
{
    // COALESCE(a, b, c) จะคืนค่าที่ไม่ใช่ NULL ค่าแรกที่พบ 
    // เราใช้ LEFT JOIN เพื่อเชื่อมตาราง Profile ทั้ง 3 ตาราง
    $query = "
        SELECT 
            u.user_id, 
            u.role, 
            u.created_at,
            
            -- ดึงชื่อจริง: เลือกชื่อจากตาราง Profile ที่มีข้อมูล (ไม่ใช่ NULL)
            COALESCE(s.first_name, t.first_name, a.first_name) AS first_name, 
            COALESCE(s.last_name, t.last_name, a.last_name) AS last_name
        FROM 
            user u
        LEFT JOIN 
            student s ON u.user_id = s.user_id AND u.role = 'student'
        LEFT JOIN 
            teacher t ON u.user_id = t.user_id AND u.role = 'teacher'
        LEFT JOIN 
            admin a ON u.user_id = a.user_id AND u.role = 'admin'
        WHERE 
            u.active = 0 
        ORDER BY 
            u.created_at DESC 
        LIMIT :limit
    ";

    $stmt = $this->conn->prepare($query);
    
    // Binding ค่า $limit: ต้องใช้ PDO::PARAM_INT สำหรับ LIMIT
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ใน models/UserModel.php

// ใน models/UserModel.php

public function updateProfile($user_id, $data)
{
    $fields = [];
    $params = ['user_id' => $user_id];
    
    // ✅ 1. เพิ่ม Logic สำหรับ Email
    if (isset($data['email'])) { 
        $fields[] = "email = :email";
        $params['email'] = $data['email'];
    }

    if (empty($fields)) return false; // ถ้าไม่มีอะไรให้อัปเดตเลย

    $query = "UPDATE user SET " . implode(", ", $fields) . " WHERE user_id = :user_id";
    $stmt = $this->conn->prepare($query);
    
    // 💡 ข้อดี: ใช้ $params ทำให้ Bind ค่าต่างๆ ได้ง่าย
    return $stmt->execute($params);
}

// ใน UserModel.php

public function countStudentsByTeacherId($teacher_id) {
    // นับจำนวนนักเรียนที่ไม่ซ้ำกัน (DISTINCT student_id)
    // ที่ลงทะเบียน (enrollment) ใน Course ที่ครูคนนี้สอน (course.teacher_id)
    $query = "
        SELECT 
            COUNT(DISTINCT e.student_id) 
        FROM 
            enrollment e
        JOIN 
            course c ON e.course_id = c.course_id
        WHERE 
            c.teacher_id = :teacher_id
    ";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':teacher_id', $teacher_id);
    $stmt->execute();
    
    return $stmt->fetchColumn();
}
}
