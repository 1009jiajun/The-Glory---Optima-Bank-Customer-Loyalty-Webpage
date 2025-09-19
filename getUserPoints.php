<?php
include "db.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_GET['user_id'])) {
    echo json_encode(['error' => 'User ID not provided']);
    exit;
}

$user_id = intval($_GET['user_id']);

try {
    $stmt = $conn->prepare("SELECT points FROM user WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['points' => (int)$row['points']]);
    } else {
        echo json_encode(['points' => 0]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error']);
}

$conn->close();
?>
