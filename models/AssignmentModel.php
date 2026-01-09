<?php
// models/AssignmentModel.php

class AssignmentModel
{
    private $conn;
    private $table = 'assignment';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ================================
    // CREATE ASSIGNMENT
    // ================================
    public function createAssignment($course_id, $title, $description, $deadline, $status = 1)
    {
        $sql = "INSERT INTO {$this->table} 
                (course_id, title, description, deadline, status)
                VALUES (:course_id, :title, :description, :deadline, :status)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':deadline', $deadline);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }

    // ================================
    // UPDATE ASSIGNMENT
    // ================================
// ใน AssignmentModel.php

public function updateAssignment($assignment_id, $course_id, $title, $deadline, $description)
{
    $query = "
        UPDATE assignment 
        SET 
            course_id = :course_id,
            title = :title,
            deadline = :deadline,
            description = :description
        WHERE 
            assignment_id = :assignment_id
    ";

    $stmt = $this->conn->prepare($query);

    // Sanitize และ Bind Parameters
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':deadline', $deadline);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        return true;
    }
    return false;
}

    // ================================
    // GET ASSIGNMENT BY ID
    // ================================
    public function getAssignmentById($assignment_id)
    {
        // 💡 ควร JOIN กับ Course และ Teacher เพื่อให้ดึงข้อมูลอ้างอิงของงานมาแสดงได้ครบถ้วน
        $query = "
        SELECT 
            a.*, 
            c.course_name, 
            c.course_code, 
            t.first_name AS teacher_first, 
            t.last_name AS teacher_last
        FROM assignment a
        JOIN course c ON a.course_id = c.course_id
        JOIN teacher t ON c.teacher_id = t.teacher_id
        WHERE a.assignment_id = :assignment_id
        LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    // ================================
    // GET ALL ASSIGNMENTS IN A COURSE
    // ================================
    public function getAssignmentsByCourse($course_id)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE course_id = :course_id
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

// ใน AssignmentModel.php

public function deleteAssignment($assignment_id)
{
    if (!is_numeric($assignment_id)) {
        return false;
    }

    try {
        // 1. เริ่ม Transaction
        $this->conn->beginTransaction();

        // A. ลบ Submissions ทั้งหมดที่เกี่ยวข้องกับ Assignment นี้
        // (จำเป็นต้องทำก่อนลบ Assignment หลัก)
        $query_submissions = "DELETE FROM submission WHERE assignment_id = :assignment_id";
        $stmt_sub = $this->conn->prepare($query_submissions);
        $stmt_sub->bindParam(':assignment_id', $assignment_id);
        $stmt_sub->execute();

        // B. ลบ Assignment หลัก
        $query_assignment = "DELETE FROM assignment WHERE assignment_id = :assignment_id";
        $stmt_ass = $this->conn->prepare($query_assignment);
        $stmt_ass->bindParam(':assignment_id', $assignment_id);
        $stmt_ass->execute();

        // 2. Commit Transaction
        $this->conn->commit();

        return true;

    } catch (PDOException $e) {
        // Rollback หากมีข้อผิดพลาด
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
        // โยน Exception เพื่อให้ Controller จับและแสดงผล
        throw new Exception("Database Transaction Failed during deletion: " . $e->getMessage()); 
    }
}


    // ใน models/AssignmentModel.php

    public function getAllAssignmentsByStudentId($student_id, $search_query = '')
    {
        $query = "
        SELECT 
            a.*, 
            c.course_name, 
            c.course_code 
        FROM assignment a
        JOIN course c ON a.course_id = c.course_id
        JOIN enrollment e ON a.course_id = e.course_id
        WHERE e.student_id = :student_id";

        // 📌 เพิ่มเงื่อนไขค้นหาด้วย LIKE
        if (!empty($search_query)) {
            $query .= " AND (a.title LIKE :search_query 
                       OR c.course_name LIKE :search_query 
                       OR c.course_code LIKE :search_query)";
        }

        $query .= " ORDER BY a.deadline DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);

        // Bind Search Query
        if (!empty($search_query)) {
            $search_param = "%" . $search_query . "%";
            $stmt->bindParam(':search_query', $search_param);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ใน AssignmentModel.php

    public function countAssignmentsByTeacherId($teacher_id)
    {
        // นับจำนวนงานทั้งหมด (assignment) 
        // ที่อยู่ใน Course ที่ครูคนนี้สอน (assignment.course_id -> course.teacher_id)
        $query = "
        SELECT 
            COUNT(a.assignment_id) 
        FROM 
            assignment a
        JOIN 
            course c ON a.course_id = c.course_id
        WHERE 
            c.teacher_id = :teacher_id
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();

        return $stmt->fetchColumn();
    }
    // ใน AssignmentModel.php

public function getRecentAssignmentsByTeacherId($teacher_id)
    {
        $query = "
        SELECT 
            a.assignment_id, 
            a.title AS assignment_title, 
            a.deadline, 
            c.course_name,
            (
                SELECT COUNT(s.submission_id) 
                FROM submission s 
                WHERE s.assignment_id = a.assignment_id AND s.status = 'Submitted' 
            ) AS submission_count -- 💡 'AS submission_count' ต้องอยู่ข้างนอก Subquery
        FROM 
            assignment a
        JOIN 
            course c ON a.course_id = c.course_id
        WHERE 
            c.teacher_id = :teacher_id
        ORDER BY 
            a.created_at DESC
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
