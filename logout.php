<?php
session_start();
session_unset();
session_destroy();
header("Location: Log_in.php");
exit();
?>
