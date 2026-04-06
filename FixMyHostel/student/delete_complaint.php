<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: ../login.php");
    exit();
}

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $check = $conn->query("SELECT * FROM complaints WHERE complaint_id='$id' AND student_id='$user_id' AND status='Pending' LIMIT 1");
    if($check->num_rows > 0){
        $row = $check->fetch_assoc();

        if(!empty($row['issue_image']) && file_exists("../uploads/" . $row['issue_image'])){
            unlink("../uploads/" . $row['issue_image']);
        }

        $conn->query("DELETE FROM complaints WHERE complaint_id='$id' AND student_id='$user_id' AND status='Pending'");
    }
}

header("Location: my_complaints.php");
exit();
?>