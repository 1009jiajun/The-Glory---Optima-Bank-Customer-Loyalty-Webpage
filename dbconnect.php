<?php
$db = "mysql:host=localhost;dbname=vouchercartsystem;charset=utf8";
$dbuser = "root";
$dbpasswd = "";

try {
    $pdo = new PDO($db, $dbuser, $dbpasswd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
