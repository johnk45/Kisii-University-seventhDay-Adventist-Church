<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gsc";

$conn = new mysqli($servername, $username, $password, $dbname);

if($conn->connect_error){
    die("connection failed: ".$conn->connect_error);

}else{
    echo "connection successful";
}
$sql = "SELECT 
EmpID,
CONCAT(LastName,' ',MiddleI,' ',FName) AS FullName,
DateOfHire,
Salary,
SkillID,
ProjectID
FROM employee  ";

$result = $conn->query($sql);

echo "<h2>Employee Information</h2>";
echo "<table border='1'>
<tr>
<th>EmpID</th>
<th>Full Name</th>
<th>Date Of Hire</th>
<th>Salary</th>
<th>SkillID</th>
<th>ProjectID</th>
</tr>";
//process the result set
if($result->num_rows > 0){
    //output data of each row
    while($row = $result->fetch_assoc()){
        echo "<tr>";
        echo "<td>".$row["EmpID"]."</td>";
        echo "<td>".$row["FullName"]."</td>";
        echo "<td>".$row["DateOfHire"]."</td>";
        echo "<td>".$row["Salary"]."</td>";
        echo "<td>".$row["SkillID"]."</td>";
        echo "<td>".$row["ProjectID"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body{
            background:#f9f9f9;
            align-items:center;
            color:#000;
        }
        .container{
            width:450px;
            margin:100px auto;
            border:none;
            box-shadow:10px 3px 2px 2px gray;
            border-radius:8px;
            background:#fff;
            padding:10px 20px;
            transform:rotateY(10deg);
            animation-name:bounceAnimation;
            animation-duration:2s;
            animation-timing-function:ease;
            animation-iteration-count:infinite;
            animation-direction:normal;
            animation-fill-mode:both;         
        }
        @keyframes bounceAnimation{
            0%,100%{transform:translateY(0);}
            50%{transform:translateY(-30px);}
        }
        input{
            padding:10px 20px;
            width:100%;
            font-size:1.2rem;
            font-weight:300;
            text-decoration:none;
            margin-bottom:2rem;
        }
        label{
            margin-bottom:2rem;
            font-size:1.5rem;
            font-weight:500;
        }
        button{
            background:green;
            width:100%;
            padding:10px 20px;
            border-radius:5px;
            font-size:1.3rem;
            border:none;
            margin:20px 0;
            color:#fff;

        }
        </style>
</head>
<body>
    <div id="errorContainer"></div>
    
    <div class="container">
        <h3>Register With Us</h3>
    <form name="regForm" onsubmit="return validateRegistrationForm()" method="post" action="register.php" enctype="multipart/form-data">
        <label for="username">Fullname:</label>
        <input type="text" name="name" id="name" placeholder="Enter your name" required>
        <label for="email">Email:</label>
            <input type="email" name="email" id="email" placeholder="Enter email Adress" required>
            <label for="phone">Phone Number:</label>
            <input type="tel" name="number" id="phone" placeholder="Enter phone number" required>
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" placeholder="Enter your username" required>
        <label for="password">Password:</label>
        <input type="password" name="passsword" id="password" placeholder="Enter your password">
        <label for="confirmpassword">Confirm Password:</label>
        <input type="password" name="password" id="confirmPassword" placeholder="Confirm password" required>

        <label>Gender:</label>
        <select name="gender" required>
            <option value="">...Select ...</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>

</select>
<label>County:</label>
<select name="country" required>
    <option value="">-- Select Country --</option>
    <option value="usa">USA</option>
    <option value="canada">Canada</option>
    <option value="argentina">Argentina</option>
</select>
<lable>Upload Profile Picture:</lable>
<input type="file" name="profileImage" accept="image /*">
        
        <button type="submit">Register</button>
</form>
</div>

<script>
const name = document.getElementById('name').value.trim();
const password = document.getElementById('password').value.trim();
const fullname = document.getElementById('fullname').value.trim();
const email = document.getElementById('email').value.trim();
const phone = document.getElementById('phone').value.trim();
const confirmPassword = form['confrimPassword'].value.trim();
const gender = form['gender'].value.trim();
const country = form['country'].value.trim();

let errors = [];
//fullname
if(name.length<2){
    errors.push("fullname must be atleast 2 characters");
}
//email
const emailPattern = '/^[^]+@[^]+\.[a-z]{2,3}$//';
if(!emailPattern.test(email)){
    errors.push("Enter a valid email adress");
}
//phone
const phonePattern = '/^[0-9]{10,15}$/';
if(!phonePattern.test(phone)){
    errors.push("Enter a valid phone number");
}
//username
const usernamePattern = '/^[a-z A-Z 0-9_]{4,15}$/';
if(!usernamePattern.test(username)){
    errors.push("Enter a valid username");
}
//password
if(password.length < 6){
    errors.push("Password must be atleast 6 characters");
}
//confirm password
if(password != confirmPassword){
    errors.push("Password do not match");
}
//gender
if(!gender){
    errors.push("please select your gender.");
}
if(!country){
    errors.push("please select country");
}
//profile Image(Optional,Validate only if selected)
if(profileImage){
    const allowedExtension = '/(\.jpeg|\.jpg|\.png|\.gif)$//';
    if(!allowedExtension.exec(profileImage)){
        errors.push("Prole image must be jpg,png,orgif");
    }
}
//shows error if any
const errorContainer = document.getElementById("errorContainer");
errorContainer.innerHTML = "";
if(errors.length>0){
    errors.forEach(err=>{
        const p = document.createElement("p");
        p.className= "error";
        p.textContent = error;
        errorContainer.appendChild(p);
    });
    return false;
}
return true;




if(name.length<2){
    alert("Username cannot be less than two characters");
}
if(password.length<5){
    alert("passowrd cannot be less than 5 characters");
}

    </script>
</body>
</html>
<?php
