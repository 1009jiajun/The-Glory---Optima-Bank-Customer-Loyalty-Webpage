<?php

include "db.php";
header('Content-Type: application/json');

// Get ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT v.id, v.title, v.points, v.image, v.description, v.terms_conditions, c.name as category
        FROM voucher v
        LEFT JOIN category c ON v.category_id = c.id
        WHERE v.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  echo json_encode($row);
} else {
  echo json_encode(["error" => "Voucher not found"]);
}

$stmt->close();
$conn->close();
?>
