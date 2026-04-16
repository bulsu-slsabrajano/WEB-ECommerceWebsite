<?php
session_start();

if (isset($_SESSION['user_id'])) {
 
    $_SESSION = array();

    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

  
    session_destroy();
}

// 4. Redirect to index.php
header("Location: ../../index.php");
exit();
?>