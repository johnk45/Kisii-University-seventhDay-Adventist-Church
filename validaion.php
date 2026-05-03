<?php
//input validation trait
trait Validation{

public function validateRequired($field,$value){
    if(empty(trim($value))){
        throw new Exception("$field is required");
    }
    return true;
}
public function validateEmail($email){
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        throw new Exception("Invalid email address");
    }
    return true;
}
public function validatePhone($phone){
    //kenyan phone numbers
    if(!preg_match('/^(07|01|\+254)[0-9]{8,9}$/',$phone)){
        throw new Exception("Invalid phone number. use format: 07xxxxxxxx");
    }
    return true;
}
public function validateDate($date,$format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format,$date);
    if(!$d || $d->format($format) !== $date){
        throw new Exception("Invalid date format");
    }
    return true;
}
public function validateNumeric($field,$value,$min = null, $max = null){
    if(!is_numeric($value)){
        throw new Exception("$field must be a number");
    }
    if($min !== null && $value < $min){
        throw new Exception("$field must be atleast $min");
    }
    if($min !== null && $value>$max){
        throw new Exception("$field must not exced $max");
    }
    return true;
}
public function validateString($field,$value,$minLength = 1,maxLength =255){
    $length = strlen(trim($value));
    if($length < $minLength){
        throw new Exception("$field must be at least $minLength characters");

    }
    if($length > $maxLength){
        throw new Exception("$filed must be atleast $maxLength characters");
    }
    return true;
}

public function sanitizeInput($data){
    if(is_array($data)){
        return array_map([$this,'sanitizeInput'],$data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data,ENT_QUOTES,'UTF-8');
    return $data;
}
}

?>