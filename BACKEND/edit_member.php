<?php

session_start();
if(!isset($_SESSION['admin'])){
    header("Location:admin_login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "church_db";

$conn = new mysqli($servername, $username, $password,$dbname );

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM members WHERE id=$id");
    $member = $result->fetch_assoc();

}
if($_SERVER["REQUEST_METHOD"] =="POST"){
    $fullname   = $_POST['fullname'];
    $email      = $_POST["email"];
    $phone      = $_POST['phone'];
    $department = $_POST['department'];

    $stmt = $conn->prepare("UPDATE members SET fullname=?, email=?,phone=?,department=? WHERE id=?");
    $stmt->bind_param("ssssi",$fullname,$email,$phone,$department,$id);

    if($stmt->execute()){
        header("Location:dashboard.php");
        exit();
    }else{
        echo "Error updatind member!";
    }

}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
</head>
<body style="font-family:Arial;padding:20px;">
<h2>Edit Member</h2>
<form method="POST">
<label for="fullname">Full Name</label><br>
<inpu type="text" name="fullname" value="<?php echo $member['fullname']; ?>" required><br><br>

<label for="email">Email:</label>
<input type="email" name="email" value="?php echo $member['email']; ?>" required><br><br>

<label for="phone">Phone</label><br>
<input type="text" name="phone" value="<?php echo $member['phone'];?>"><br><br>

<label for="department">Department</label>
<input type="text" name="department" value="<?php echo $member['department'];?>"><br><br>

<button type="submit">Update Member</button>

</form>


</body>
</html>