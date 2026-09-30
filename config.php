<?php
/**
 * Database connection for XAMPP (MySQL)
 * Default XAMPP MySQL user: root, password: (empty)
 */
$host = "localhost";
$user = "root";
$pass = "";
$db   = "project_submission";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed. Start Apache & MySQL in XAMPP, then import sql/setup.sql in phpMyAdmin.");
}

mysqli_set_charset($conn, "utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
