<?php
session_start();
session_destroy();
header('Location: /bioskop-ticketing/auth/login.php');
exit;
?>
