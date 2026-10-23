<?php
/**
 * User Logout
 * Knowledge Base Portal
 */
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit;

