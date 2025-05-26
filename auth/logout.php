<?php
require_once '../api/config.php';

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: ../index.php");
exit;
?>