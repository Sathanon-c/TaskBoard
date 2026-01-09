<?php
class CourseModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // สร้างวิชา
    public function createCourse($course_code, $course_name, $level,$class_id, $course_detail, $teacher_id, $status = 1)
    {
        $sql = "INSERT INTO course (course_code, course_name, level,class_id, course_detail, teacher_id, status, created_at) 
                VALUES (:course_code, :course_name, :level,:class_id, :course_detail, :teacher_id, :status, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':course_code', $course_code);
        $stmt->bindParam(':course_name', $course_name);
        $stmt->bindParam(':level', $level);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':course_detail', $course_detail);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getCoursesByUserId($user_id, $level = '', $search = '')
    {
        // 1. ดึง teacher_id จาก user_id
        $stmt = $this->conn->prepare("SELECT teacher_id FROM teacher WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$teacher) return [];

        $teacher_id = $teacher['teacher_id'];

        // 2. Query หลัก - เพิ่ม Subquery COUNT(e.student_id)
        $sql = "
            SELECT 
                c.*, 
                (
                    SELECT COUNT(e.student_id) 
                    FROM enrollment e 
                    WHERE e.course_id = c.course_id
                ) AS student_count  
            FROM 
                course c 
            WHERE 
                c.teacher_id = :teacher_id
        ";
        $params = [':teacher_id' => $teacher_id];

        // Filter by level
        if (!empty($level)) {
            $sql .= " AND c.level = :level";
            $params[':level'] = $level;
        }

        // Search
        if (!empty($search)) {
            $sql .= " AND (c.course_name LIKE :search OR c.course_code LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $val) {
            // ใช้ bindValue เนื่องจาก :search มี % หุ้มอยู่
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourses($level = '', $search = '')
    {
        $sql = "SELECT * FROM course WHERE 1";

        $params = [];

        // Filter level
        if (!empty($level)) {
            $sql .= " AND level = :level";
            $params[':level'] = $level;
        }

        // Search (ค้นหาชื่อคอร์ส / code)
        if (!empty($search)) {
            $sql .= " AND (course_name LIKE :search OR course_code LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึง level ของ courses ทั้งหมดของครู
    public function getLevelsByUserId($user_id)
    {
        $stmt = $this->conn->prepare("SELECT teacher_id FROM teacher WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$teacher) return [];

        $teacher_id = $teacher['teacher_id'];

        $stmt = $this->conn->prepare("SELECT DISTINCT level FROM course WHERE teacher_id = :teacher_id ORDER BY level ASC");
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }


    public function getCourseById($course_id)
    {
        $query = "
        SELECT 
            c.*, 
            t.first_name AS teacher_first,  
            t.last_name AS teacher_last     
        FROM 
            course c
        JOIN 
            teacher t ON c.teacher_id = t.teacher_id  
        WHERE 
            c.course_id = :course_id
        LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // อัปเดตวิชา
    public function updateCourse($course_id, $data)
    {
        $fields = [];
        $params = ['course_id' => $course_id];

        if (!empty($data['course_code'])) {
            $fields[] = "course_code = :course_code";
            $params['course_code'] = $data['course_code'];
        }
        if (!empty($data['course_name'])) {
            $fields[] = "course_name = :course_name";
            $params['course_name'] = $data['course_name'];
        }
        if (isset($data['level'])) {
            $fields[] = "level = :level";
            $params['level'] = $data['level'];
        }
        if (isset($data['course_detail'])) {
            $fields[] = "course_detail = :course_detail";
            $params['course_detail'] = $data['course_detail'];
        }

        if (empty($fields)) return false;

        $sql = "UPDATE course SET " . implode(", ", $fields) . " WHERE course_id = :course_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function getAllCourses()
    {
        $query = "
        SELECT 
            c.course_id,
            c.course_name,
            c.status,
            c.created_at,
            CONCAT(t.first_name, ' ', t.last_name) AS instructor,
            (SELECT COUNT(e.student_id) FROM enrollment e WHERE e.course_id = c.course_id) AS students
        FROM course c
        JOIN teacher t ON c.teacher_id = t.teacher_id
        ORDER BY c.created_at DESC"; // 📌 ลบ LIMIT ออก

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // ใน CourseModel.php
    public function isCourseOwner($course_id, $user_id)
    {
        $query = "
        SELECT 
            COUNT(c.course_id) 
        FROM 
            course c 
        JOIN 
            teacher t ON c.teacher_id = t.teacher_id
        WHERE 
            c.course_id = :course_id AND t.user_id = :user_id
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // คืนค่า true ถ้ามี (Count > 0)
        return $stmt->fetchColumn() > 0;
    }

    // ใน CourseModel.php
    public function deleteCourse($course_id)
{
    // ตรวจสอบให้แน่ใจว่า Course ID เป็นตัวเลข เพื่อป้องกัน SQL Injection (แม้ว่า bindParam จะช่วยแล้วก็ตาม)
    if (!is_numeric($course_id)) {
        return false;
    }

    try {
        // 1. เริ่ม Transaction
        $this->conn->beginTransaction();

        // **A. ลบข้อมูลในตารางลูก (Child Tables) ตามลำดับ**

        // 1. ลบ Submissions ที่เกี่ยวข้องกับ Assignments ใน Course นี้
        // (สมมติว่าตาราง submission มี FK ไปยัง assignment_id)
        $query_submissions = "
            DELETE s FROM submission s
            JOIN assignment a ON s.assignment_id = a.assignment_id
            WHERE a.course_id = :course_id
        ";
        $stmt = $this->conn->prepare($query_submissions);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        
        // 2. ลบ Assignments ทั้งหมดใน Course นี้
        $query_assignments = "DELETE FROM assignment WHERE course_id = :course_id";
        $stmt = $this->conn->prepare($query_assignments);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();

        // 3. ลบ Course Enrollment (นักเรียนที่ลงทะเบียน)
        $query_enrollment = "DELETE FROM enrollment WHERE course_id = :course_id";
        $stmt = $this->conn->prepare($query_enrollment);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();

        // **B. ลบ Course หลัก (Parent Table)**
        
        // 4. ลบ Course หลัก
        $query_course = "DELETE FROM course WHERE course_id = :course_id";
        $stmt = $this->conn->prepare($query_course);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();

        // 5. Commit Transaction (ยืนยันการเปลี่ยนแปลงทั้งหมด)
        $this->conn->commit();

        return true;

    } catch (PDOException $e) {
        // หากมีข้อผิดพลาดใดๆ ให้ยกเลิกการเปลี่ยนแปลงทั้งหมด
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
        // ในสภาพแวดล้อมจริง ควรบันทึก $e->getMessage() ลงใน log
        // throw new Exception("Database error: " . $e->getMessage()); 
        return false;
    }
}
}
