<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$fullName = $data['fullName'] ?? null;
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

// ✅ Check if user already exists
$check = $conn->prepare("SELECT id FROM user WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already registered"]);
    exit;
}

// ✅ Hash password
// $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ✅ Insert user
$stmt = $conn->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $fullName, $email, $password);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Registration successful"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to register"]);
}

$stmt->close();
$conn->close();
?>
