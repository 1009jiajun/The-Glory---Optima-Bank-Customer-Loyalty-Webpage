<?php
header("Content-Type: application/json");

include "db.php";

// Read JSON
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "No JSON received"]);
    exit;
}

$email = $data['email'] ?? null;
$username = $data['username'] ?? null;
$profile = $data['profile_image'] ?? null;
$uid = $data['uid'] ?? null;
$userId = md5($email); // unique ID for filename

// Download & cache profile image locally
if ($profile && strpos($profile, 'googleusercontent') !== false) {
    $imgData = file_get_contents($profile);
    $fileName = "assets/img/profile/" . $userId . ".jpg";
    file_put_contents($fileName, $imgData);
    $profile = $fileName; // overwrite with local path
} else {
    $profile = "assets/img/default-avatar.png";
}

if (!$email || !$username) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// Insert or update user
$stmt = $conn->prepare("INSERT INTO `user` (email, username, profile_image, firebase_uid)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE username = VALUES(username), profile_image = VALUES(profile_image)");
$stmt->bind_param("ssss", $email, $username, $profile, $uid);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>
