<?php
include_once('../config/Database.php');
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q_id = $_POST['question_id'];
    $exam_id = $_POST['exam_id'];
    $course_id = $_POST['course_id'];
    
    $query = "UPDATE questions SET 
                question_text = :txt, 
                option_1 = :o1, option_2 = :o2, option_3 = :o3, option_4 = :o4, 
                correct_option = :correct 
              WHERE question_id = :id";
              
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':txt' => $_POST['question_text'],
        ':o1' => $_POST['option_1'],
        ':o2' => $_POST['option_2'],
        ':o3' => $_POST['option_3'],
        ':o4' => $_POST['option_4'],
        ':correct' => $_POST['correct_option'],
        ':id' => $q_id
    ]);

    header("Location: ../views/teacher/AddQuestions.php?exam_id=$exam_id&course_id=$course_id");
}