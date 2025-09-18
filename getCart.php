<?php
header("Content-Type: application/json");

include "db.php";

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$sql = "SELECT c.id, c.quantity, v.id as voucher_id, v.title, v.points, v.image
        FROM cartitems c
        JOIN voucher v ON c.voucher_id = v.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
  $items[] = $row;
}
// Return updated cart count for this user (use cartitems!)
$countSql = "SELECT SUM(quantity) as totalItems FROM cartitems WHERE user_id = ?";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalItems = 0;
if ($countRow = $countResult->fetch_assoc()) {
    $totalItems = intval($countRow['totalItems']);
}
$countStmt->close();

echo json_encode([
    "status" => "success",
    "items" => $items,
    "totalItems" => $totalItems
]);

$stmt->close();
$conn->close();
?>
