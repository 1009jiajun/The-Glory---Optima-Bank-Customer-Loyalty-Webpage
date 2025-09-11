<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php"; // your database connection

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? null;
$password = $data['password'] ?? null;

if (!$token || !$password) {
    echo json_encode(["status" => "error", "message" => "Token and password are required"]);
    exit;
}

// Step 1: Verify token
$stmt = $conn->prepare("SELECT email, expiry FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
    exit;
}

$row = $result->fetch_assoc();
$email = $row['email'];
$expiry = $row['expiry'];

if (strtotime($expiry) < time()) {
    echo json_encode(["status" => "error", "message" => "Token has expired"]);
    exit;
}

// Step 2: Hash new password
// $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Step 3: Update user password
$update = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
$update->bind_param("ss", $password, $email);

if ($update->execute()) {
    // Step 4: Delete token after successful reset
    $delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
    $delete->bind_param("s", $token);
    $delete->execute();

    echo json_encode(["status" => "success", "message" => "Password updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update password"]);
}

$stmt->close();
$update->close();
$conn->close();
?>
