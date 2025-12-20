<?php
session_start();

function checkLogin() {
    if (!isset($_SESSION['User_ID'])) {
        header("Location: Log_in.php");
        exit();
    }
}

function hasRole($required_roles) {
    if (!isset($_SESSION['Permission'])) {
        return false;
    }
    
    // If string, convert to array
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    
    return in_array($_SESSION['Permission'], $required_roles);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>
