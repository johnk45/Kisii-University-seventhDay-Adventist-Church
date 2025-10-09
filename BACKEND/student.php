<?php 
//Database Connection
$host='localhost';
$db="";
$user='root';
$pass='';

$dsn="mysql:host=$host;dbname=$db";charset=utf8mb4_;
try{
$pdo=new PDO($dsn,$user,$pass);
}catch(PDOException $e){
die("DB connection failed:".$e->getMessage());
}

//Handles POST REQUEST
if($_SERVER["REQUEST-METHOD"] == 'POST'){
    //sanitize and retrieve inputs
    $name=trim($_POST["fullname"]);
    $username=trim($_POST["username"]);
    $phone=trim($_POST["phone"]);
    $email=trim($_POST["email"]);
    $password=trim($_POST["password"]);
    $county=trim($_POST["county"]);
    $confirmpassword=trim($_POST["confirmpassword"]);

    $errors=[];

    //serverside validation
    if(!email)$errors[]="Invalid email adress!";
    if(strlen($name)<2)$errors[]="Fullname must be atleast 2 characters";
    if(!preg_match('/^[0-9](10,15)$/',$phone))$errors[]="invalid phone number";
    if(!preg_match('/^[a-z A-Z 0-9-]{4,15}&/',$username))$errors[]="Invalid username!";
    if(strlen($password)<6 || !preg_match('/[A-Z]/',$password) ||!preg_match('/[a-z]/',$password))$errors[]="password must be atleast 6 characters";
    if($password!=$confirmpassword) $errors[]="password does not match";


    //insert into the database
    if(empty(errors)){
        $hashedpassword=password_hash($password,PASSWORD_BCRYPT);
        $sql="INSERT INTO (table name)(fullname,username,phone,email,county,password,confirmpassword")
        VALUES(:fullname,:username,:phone,:email,:county,:password,:confircmpassword);
        
        $stmt=$pdo->prepare($sql);
        $stmt->execute([
            ':fullname'=>$name,
            ':username'=>$username,
            ':phone'=>$phone,
            ':email'=>$email,
            ':county'=>$county,
            ':password'=>$password,
            ':confirmpassword'=>$confirmpassword

        ]);
        echo"<h3>Registration sucessful!</h3>";

        //show errors
        for each($errors as $err){
            echo"<p style="color:red;">$err</p>";
        }

    
        
        
    }

}


?>