<?php
//database connection details
$servername="localhost";
$username="root";
$password="";
$dbname="KSUSDA";


//create connection
$conn=new mysqli($servername,$username,$password,$dbname);

//chech connection
if($conn->connect_error){
    die("connection failed".$conn->connect_error);
}
//insert data into thhe database
$fullname=$_POST["fullname"];
$phone=$_POST["phone"];
$amount=$_POST["amount"];
$payment=$_POST["payment"];
$purpose=$_POST["purpose"];
$message=$_POST["message"];


//insert into the database
$sql="INSERT INTO Church_COntr(fullname,phone,amount,payment,purpose,message) VALUES('$fullname','$phone','$amount','$payment','$purpose','$message')";

if($conn->query($sql)  === TRUE){
    echo"Created sucessfully";

}else{
    echo"Error:".$sql."<br>".$conn->error;
}
$conn->close(); 










?>