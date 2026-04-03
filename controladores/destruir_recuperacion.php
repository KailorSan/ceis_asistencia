<?php
session_start();
unset($_SESSION['paso_recuperacion']);
unset($_SESSION['recup_temp']);
unset($_SESSION['error_recup']);

header("Location: ../vistas/login.php");
exit;
?>