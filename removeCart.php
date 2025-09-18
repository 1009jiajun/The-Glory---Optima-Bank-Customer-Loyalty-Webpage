<?php
include "db.php";
header("Content-Type: application/json");

// Ensure ID is provided
$cartItemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($cartItemId <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid cart item ID"]);
    exit;
}

// Get user_id before deleting (so we can recalc totalItems)
$getUserSql = "SELECT user_id FROM cartitems WHERE voucher_id = ?";
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
    // Delete the cart item
    $sql = "DELETE FROM cartitems WHERE voucher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cartItemId);
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

    echo json_encode(["status" => "success", "message" => "Item removed", "totalItems" => $totalItems]);
} else {
    echo json_encode(["status" => "error", "message" => "Cart item not found"]);
}

$conn->close();
?>
