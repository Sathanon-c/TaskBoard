<?php
class EnrollmentModel
{
    private $conn;
    private $table = "enrollment";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ----------------------------------------
    // ดึงนักศึกษาที่อยู่ในรายวิชานี้
    // ----------------------------------------
public function getStudentsByCourse($course_id)
{
    $sql = "
        SELECT 
            s.student_id, 
            s.student_code,
            s.first_name,
            s.last_name, 
            s.major, 
            s.year,
            s.class_id,
            s.user_id,      -- ✅ เพิ่ม user_id (สมมติว่ามีในตาราง student)
            u.email         -- ✅ เพิ่ม email (โดย JOIN กับตาราง user)
        FROM enrollment e
        JOIN student s ON e.student_id = s.student_id
        JOIN user u ON s.user_id = u.user_id  -- ✅ JOIN ตาราง user เพื่อดึง email
        WHERE e.course_id = :course_id
        ORDER BY s.first_name ASC
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // ----------------------------------------
    // เพิ่มนักศึกษาเข้า course
    // ----------------------------------------
    public function addStudent($course_id, $student_id)
    {
        $sql = "
            INSERT INTO {$this->table} (course_id, student_id)
            VALUES (:course_id, :student_id)
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'course_id' => $course_id,
            'student_id' => $student_id
        ]);
    }

    // ----------------------------------------
    // เอานักศึกษาออกจาก course
    // ----------------------------------------
    public function removeStudent($course_id, $student_id)
    {
        $sql = "
            DELETE FROM {$this->table}
            WHERE course_id = :course_id AND student_id = :student_id
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'course_id' => $course_id,
            'student_id' => $student_id
        ]);
    }

    // ----------------------------------------
    // เช็คว่าซ้ำหรือยัง
    // ----------------------------------------
    public function exists($course_id, $student_id)
    {
        $sql = "
            SELECT id FROM {$this->table}
            WHERE course_id = :course_id AND student_id = :student_id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'course_id' => $course_id,
            'student_id' => $student_id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // ใน models/EnrollmentModel.php (ตัวอย่างเมธอดที่ใช้ใน Controller ข้างต้น)

public function getCoursesByStudent($student_id)
{
    $query = "
        SELECT 
            c.course_id, 
            c.course_code, 
            c.course_name, 
            t.first_name AS teacher_first,
            t.last_name AS teacher_last
        FROM 
            enrollment e
        JOIN 
            course c ON e.course_id = c.course_id
        JOIN 
            teacher t ON c.teacher_id = t.teacher_id 
        WHERE 
            e.student_id = :student_id
        ORDER BY 
            c.course_code ASC
    ";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function searchEnrolledCourses($student_id, $search = "")
{
    $sql = "
        SELECT 
            c.course_id, 
            c.course_code, 
            c.course_name, 
            t.first_name AS teacher_first, 
            t.last_name AS teacher_last
        FROM 
            enrollment e
        JOIN 
            course c ON e.course_id = c.course_id
        JOIN 
            teacher t ON c.teacher_id = t.teacher_id
        WHERE 
            e.student_id = :student_id 
    ";

    $params = [':student_id' => $student_id];

    if (!empty($search)) {
        $search_term = "%" . $search . "%";
        
        $sql .= "
            AND (
                c.course_name LIKE :search OR
                c.course_code LIKE :search OR
                t.first_name LIKE :search OR
                t.last_name LIKE :search
            )
        ";
        $params[':search'] = $search_term;
    }

    $sql .= " ORDER BY c.course_name ASC";

    $stmt = $this->conn->prepare($sql);
    
    // PDO::execute สามารถรับ array parameters ได้โดยตรง
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}

