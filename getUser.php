<?php
include "db.php";

$uid = $_GET['uid'];

$stmt = $conn->prepare("SELECT id, email, username, profile_image FROM user WHERE firebase_uid=?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["error" => "User not found"]);
}
