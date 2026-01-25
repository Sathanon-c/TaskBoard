<?php
class ExamModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- ส่วนของอาจารย์ ---

    // สร้างหัวข้อข้อสอบ
    public function createExam($course_id, $title, $duration) {
        $query = "INSERT INTO exams (course_id, title, duration_minutes) VALUES (:course_id, :title, :duration)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':duration', $duration);
        return $stmt->execute();
    }

    // เพิ่มโจทย์ (แบบยุบรวมตัวเลือก)
    public function addQuestion($exam_id, $text, $opt1, $opt2, $opt3, $opt4, $correct, $points) {
        $query = "INSERT INTO questions (exam_id, question_text, option_1, option_2, option_3, option_4, correct_option, points) 
                  VALUES (:exam_id, :text, :opt1, :opt2, :opt3, :opt4, :correct, :points)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':exam_id' => $exam_id,
            ':text' => $text,
            ':opt1' => $opt1,
            ':opt2' => $opt2,
            ':opt3' => $opt3,
            ':opt4' => $opt4,
            ':correct' => $correct,
            ':points' => $points
        ]);
        return $stmt;
    }

    // ดึงรายการข้อสอบทั้งหมดในวิชา
    public function getExamsByCourse($course_id) {
        $query = "SELECT * FROM exams WHERE course_id = :course_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- ส่วนของนักศึกษา ---

    // ดึงโจทย์ทั้งหมดของข้อสอบนั้นๆ มาทำ
    public function getQuestionsByExam($exam_id) {
        $query = "SELECT * FROM questions WHERE exam_id = :exam_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':exam_id', $exam_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // บันทึกผลสอบ
    public function saveResult($exam_id, $student_id, $score) {
        $query = "INSERT INTO exam_results (exam_id, student_id, score_obtained) VALUES (:exam_id, :student_id, :score)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':exam_id' => $exam_id,
            ':student_id' => $student_id,
            ':score' => $score
        ]);
    }

    public function getAllExamsForStudent($student_id, $search = '') {
    // ดึงข้อสอบในทุกรายวิชาที่นักศึกษาคนนี้ลงทะเบียนเรียน
    $query = "SELECT e.*, c.course_name, c.course_code 
              FROM exams e
              JOIN course c ON e.course_id = c.course_id
              JOIN enrollment en ON c.course_id = en.course_id
              WHERE en.student_id = :student_id 
              AND (e.title LIKE :search OR c.course_name LIKE :search)";
    
    $stmt = $this->conn->prepare($query);
    $search_param = "%$search%";
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':search', $search_param);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getStudentResult($exam_id, $student_id) {
    $query = "SELECT * FROM exam_results WHERE exam_id = :exam_id AND student_id = :student_id LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([':exam_id' => $exam_id, ':student_id' => $student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function getTotalExamScore($exam_id) {
    $query = "SELECT SUM(points) as total FROM questions WHERE exam_id = :exam_id";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([':exam_id' => $exam_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}
}