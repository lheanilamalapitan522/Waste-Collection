<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

session_unset();
session_destroy();
header('Location: index.php');
exit;
?>