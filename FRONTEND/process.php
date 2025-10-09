<?php
$servername="localhost";
$username= "root";
$password="";
$dbname =" ";

$conn = new mysqli($servername,$username,$password,$dbname);
if($conn->connect_error){
    die("connection failed:".$connect_error);}
    }
    $name = $conn->real_escape_string($_POST['name']);
    $contact = $connect->real_escape_string($_POST['contact']);
    $message = $connect->real_escape_string($_POST['message']);
    $is_confidential = intval($_POST['is_confidential']);

    $sql = "INSERT  INTO table_name(name,contact,is_confidential) VALUES('$name','$contact','$message','$is_confidential')";

    if($conn->query($sql) === TRUE){
        echo "<script>alert('Your payer request has been submitted sucessfully. Thank You!"); window.location='index.html';</script>

    }else{
        echo "Error:".$conn->error;
    }
    $conn->close();
?>