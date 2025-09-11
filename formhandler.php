<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$username = $_POST['fullName'];
$password = $_POST['password'];
$email = $_POST['email'];

try {
require_once 'dbconnect.php';
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
}
else
{
    header("location:index.html");
}