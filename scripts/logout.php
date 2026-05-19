<?php
session_start();
session_destroy();
header("Location: ../procesos/login.php");
exit();
?>
