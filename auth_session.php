<?php
session_start();
// not yet understood
function checkLogin() {
    if (!isset($_SESSION['User_ID'])) {
        header("Location: Log_in.php");
        exit();
    }
}
// understood
// this function returns true if the user permission is in the required permissions array
function hasRole($required_roles) {
    // isset returns false when null(so this code determine if there is a user or not like not logged in)
    if (!isset($_SESSION['Permission'])) {
        return false;
    }
    
    // If string, convert to array
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    // checks if the permission of the user in the premission array fetched form the sidebar.php
    // returns true if the user has the required permission to see the link in side bar
    return in_array($_SESSION['Permission'], $required_roles);
}
// not yet understood
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>
