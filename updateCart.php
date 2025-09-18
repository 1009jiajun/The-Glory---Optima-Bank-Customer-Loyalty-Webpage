<?php
include "db.php";
header("Content-Type: application/json");

$cartItemId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$newQuantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;

if ($cartItemId <= 0 || $newQuantity < 1) {
    echo json_encode(["status" => "error", "message" => "Invalid ID or quantity"]);
    exit;
}

// Get user_id before update (so we can recalc totalItems)
$getUserSql = "SELECT user_id FROM cartitems WHERE id = ?";
$getStmt = $conn->prepare($getUserSql);
$getStmt->bind_param("i", $cartItemId);
$getStmt->execute();
$getResult = $getStmt->get_result();
$user_id = 0;
if ($row = $getResult->fetch_assoc()) {
    $user_id = $row['user_id'];
}
$getStmt->close();

if ($user_id > 0) {
    // Update quantity
    $sql = "UPDATE cartitems SET quantity = ?, added_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $newQuantity, $cartItemId);
    $stmt->execute();
    $stmt->close();

    // Recalculate total items
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

    echo json_encode(["status" => "success", "message" => "Quantity updated", "totalItems" => $totalItems]);
} else {
    echo json_encode(["status" => "error", "message" => "Cart item not found"]);
}

$conn->close();
?>
