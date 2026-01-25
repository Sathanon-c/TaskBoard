<?php

class SubmissionModel
{
    private $conn;
    private $table = 'submission';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createSubmission($assignment_id, $student_id, $file_path)
    {

        $query = "
            INSERT INTO " . $this->table . "
            (assignment_id, student_id, file_path, status, submitted_at)
            VALUES (:assignment_id, :student_id, :file_path, 'Submitted', NOW())
            ON DUPLICATE KEY UPDATE 
                file_path = VALUES(file_path),
                status = 'Submitted',
                submitted_at = NOW(),
                teacher_feedback = NULL; -- ล้าง Feedback เดิมเมื่อมีการส่งงานใหม่/แก้ไข
        ";

        $stmt = $this->conn->prepare($query);

        // ทำความสะอาดข้อมูล
        $file_path = htmlspecialchars(strip_tags($file_path));

        $stmt->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':file_path', $file_path);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getSubmissionStatus($assignment_id, $student_id)
    {

        $query = "
            SELECT 
                submission_id, 
                file_path, 
                status, 
                submitted_at, 
                teacher_feedback
            FROM " . $this->table . " 
            WHERE assignment_id = :assignment_id AND student_id = :student_id 
            LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

public function getStudentsAndSubmissionStatus($assignment_id, $course_id)
{
    $query = "
    SELECT
        s.student_id,
        s.first_name,
        s.last_name,
        sub.submission_id,
        sub.status,
        sub.submitted_at,
        sub.file_path,
        sub.teacher_feedback,
        sub.score -- เพิ่มบรรทัดนี้เพื่อดึงคะแนนออกมา
    FROM student s
    JOIN enrollment e ON s.student_id = e.student_id
    LEFT JOIN submission sub 
        ON s.student_id = sub.student_id 
        AND sub.assignment_id = :assignment_id
    WHERE e.course_id = :course_id
    ORDER BY s.last_name ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // ใน models/SubmissionModel.php (เพิ่มเมธอด)

public function getSubmissionDetailById($submission_id)
{
    $query = "
    SELECT 
        sub.*, 
        s.first_name AS student_first, 
        s.last_name AS student_last,
        a.title AS assignment_title,
        a.max_score -- เพิ่มบรรทัดนี้เพื่อดึงคะแนนเต็มมาแสดง
    FROM submission sub
    JOIN student s ON sub.student_id = s.student_id  
    JOIN assignment a ON sub.assignment_id = a.assignment_id
    WHERE sub.submission_id = :submission_id
    LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':submission_id', $submission_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    // 💡 (เมธอด updateSubmissionFeedback ก็ต้องมี ตามที่เคยแนะนำไปก่อนหน้านี้)
    // ใน models/SubmissionModel.php
    public function updateSubmissionFeedback($submission_id, $feedback, $status)
    {
        $query = "
        UPDATE " . $this->table . "
        SET teacher_feedback = :feedback, 
            status = :status
        WHERE submission_id = :submission_id";

        $stmt = $this->conn->prepare($query);

        // ทำความสะอาดข้อมูล
        $feedback = htmlspecialchars(strip_tags($feedback));
        $status = trim(htmlspecialchars(strip_tags($status))); // 💡 เพิ่ม trim()
        // Bind parameters
        $stmt->bindParam(':feedback', $feedback);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':submission_id', $submission_id, PDO::PARAM_INT); // 💡 ต้อง Bind เป็น INT

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateSubmissionGrade($submission_id, $feedback, $status, $score)
{
    $query = "UPDATE submission 
              SET teacher_feedback = :feedback, 
                  status = :status, 
                  score = :score,
                  graded_at = NOW() 
              WHERE submission_id = :submission_id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(':feedback', $feedback);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':score', $score);
    $stmt->bindParam(':submission_id', $submission_id, PDO::PARAM_INT);

    return $stmt->execute();
}

public function getStudentGradesByCourse($course_id, $student_id) {
    $query = "
    SELECT 
        a.title AS assignment_title,
        a.max_score,
        sub.score AS student_score,
        sub.status,
        sub.submitted_at
    FROM assignment a
    LEFT JOIN submission sub ON a.assignment_id = sub.assignment_id AND sub.student_id = :student_id
    WHERE a.course_id = :course_id
    ORDER BY a.created_at ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
