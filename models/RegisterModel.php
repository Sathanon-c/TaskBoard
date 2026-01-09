<?php

class CreateStudent {

    private $conn;

    private $first_name;
    private $last_name;
    private $student_code;
    private $email;
    private $password;
    private $major;
    private $year;
    private $phone;
    private $gender;
    private $class_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ---------------- SETTERS ----------------
    public function setFirstname($first_name){ $this->first_name = trim($first_name); }
    public function setLastname($last_name){ $this->last_name = trim($last_name); }
    public function setStudentCode($student_code){ $this->student_code = trim($student_code); }
    public function setEmail($email){ $this->email = trim($email); }
    public function setMajor($major){ $this->major = trim($major); }
    public function setYear($year){ $this->year = trim($year); }
    public function setPhone($phone){ $this->phone = trim($phone); }
    public function setGender($gender){ $this->gender = trim($gender); }
    public function setPassword($password){ $this->password = $password; }
    public function setClassId($class_id){ $this->class_id = trim($class_id); }

    public function checkPasswordLength() {
        return strlen($this->password) >= 6;
    }

    public function checkEmail() {
        $sql = "SELECT user_id FROM user WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function checkStudentCodeExists() {
        $sql = "SELECT student_id FROM student WHERE student_code = :student_code LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":student_code", $this->student_code);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function create() {
        try {
            $this->conn->beginTransaction();

            // 1️⃣ Insert into user
            $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);

            $sql_user = "INSERT INTO user (email, password, role, active, created_at)
                         VALUES (:email, :password, 'student', 1, NOW())";

            $stmt_user = $this->conn->prepare($sql_user);
            $stmt_user->bindParam(":email", $this->email);
            $stmt_user->bindParam(":password", $hashed_password);

            $stmt_user->execute();

            $user_id = $this->conn->lastInsertId(); // FK for student


            // 2️⃣ Insert into student
            $sql_student = "INSERT INTO student
                (user_id, first_name, last_name, student_code, major, year, class_id, phone, gender)
                VALUES 
                (:user_id, :first_name, :last_name, :student_code, :major, :year, :class_id, :phone, :gender)"; // 📌 เพิ่ม class_id ในคอลัมน์และ VALUES

            $stmt_student = $this->conn->prepare($sql_student);

            $stmt_student->bindParam(":user_id", $user_id);
            $stmt_student->bindParam(":first_name", $this->first_name);
            $stmt_student->bindParam(":last_name", $this->last_name);
            $stmt_student->bindParam(":student_code", $this->student_code);
            $stmt_student->bindParam(":major", $this->major);
            $stmt_student->bindParam(":year", $this->year);
            $stmt_student->bindParam(":class_id", $this->class_id); // 📌 Bind Class ID
            $stmt_student->bindParam(":phone", $this->phone);
            $stmt_student->bindParam(":gender", $this->gender);

            $stmt_student->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {

            $this->conn->rollBack();
            error_log("Create Student Error: " . $e->getMessage());
            return false;
        }
    }
}