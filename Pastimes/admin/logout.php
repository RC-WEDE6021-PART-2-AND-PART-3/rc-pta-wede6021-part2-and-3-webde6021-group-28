<?php
/**
 * admin/logout.php — Destroy admin session and redirect to admin login.
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 */

session_start();
session_destroy();

header('Location: login.php');
exit();
?>