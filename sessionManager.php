<?php
class sessionManager{
    //configures php session setting tomake them secure before they start
    public static function startsSecureSession(){
        //set a secure session parameters
        init_set('session.cookie_httponly',1); //protects js from using cookies
        init_set('session.use_only_cookie',1); //make sure the session us eonly cookies
        init_set('session.cookie_secure',isset($_SERVER['HTTPS'])); //Sends cookies over https
        init_set('session.use_strict_mode',1); //prevents session fixation attacks
        init_set('session.cookie_samesite','Strict'); //prevents cross-site request attacks

        if(session_start() === PHP_SESSION_NONE){
            session_start();
            
            //validate session
            self::validateSession();  //checks if session is valid
        }
//this a function thats vaildate the session
        public static function validateSession(){
            if(!isset($_SESSION['user_id'])){
                return false;
            }
        }
//session timeout(30minutes)
if(isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']>1800)){
    self::destroySession();
    header("Location: login.php?timeout=1");
}
   
//check ip address consistency
if(isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']){
    self::destroySession();
    Security::logSecurityEvent('IP Mismatch - Possible Hijacking');
    header("Location: login.php");
    exit();
}
//check user agent constistency
if(isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']){
    self::destroySession();
    Security::logSecurityEvent('User Agent Mismatch - Possible Hijacking');
    header("Location: login.php");
    exit();
}
//Update last activity
$_SESSION['login_time'] == time();

return true;
    }

    public static function destroySession(){
        $_SESSION = array();

        if(init_get("session.use_cookies")){
            $params = session_get_cookie_params();
            setcookie(session_name(),'',time() - 42000,
            $params["path"],$params["domain"],
            $params["secure"],$params["httponly"]);
        }
        session_destroy();
    }
    public static function regenerateSession(){
        session_regenerate_id(true);
        $_SESSION['session_id'] = session_id();
        $_SESSION['session_regenerated'] = time();
    }
}

?>
