<?php 
session_start();
if(!isset($_SESSION['admin'])) {
    header("Location:admin_login.html");
    exit();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "church_db";

$conn = new mysqli($servername,$username,$password, $dbname);

if(isset($_GET['id']));
$id = intval($_GET['id']);
$stmt = $conn->prepare("DELETE FROM members WHERE id=?");
$stmt -> bind_param("i",$id);

if($stmt->execute()){
    header("Location:dashboard.php");
    exit();
}else{
    echo "Error deleting member!";
}
}

?>