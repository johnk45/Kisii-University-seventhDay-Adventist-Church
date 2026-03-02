<?php
$target_dir = 'uploads/'
$target_file = $target_dir.basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
//CHECK IF IMAGE IS ACTUALLY ONE OR FAKE
if(isset($_POST["submit"])){
    echo"File is an image-".$check["mime"]. ".";
    $uploadOk = 1;
}else{
    echo"File is not an image";
    $uploadOk = 0;
}




?>