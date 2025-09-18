<?php
// addToCart.php
include "db.php";
header("Content-Type: application/json");

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// Get JSON body (if sent as raw JSON)
$input = json_decode(file_get_contents("php://input"), true);

// Or fallback to POST form data
$voucher_id = isset($input['voucher_id']) ? intval($input['voucher_id']) : (isset($_POST['voucher_id']) ? intval($_POST['voucher_id']) : 0);
$user_id    = isset($input['user_id']) ? intval($input['user_id']) : (isset($_POST['user_id']) ? intval($_POST['user_id']) : 0);
$quantity   = isset($input['quantity']) ? intval($input['quantity']) : (isset($_POST['quantity']) ? intval($_POST['quantity']) : 1);

if ($voucher_id <= 0 || $user_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid voucher_id or user_id"]);
    exit;
}

// Check if item already exists in cartitems
$checkSql = "SELECT id, quantity FROM cartitems WHERE voucher_id = ? AND user_id = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("ii", $voucher_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Update quantity if exists
    $newQuantity = $row['quantity'] + $quantity;
    $updateSql = "UPDATE cartitems SET quantity = ?, added_at = CURRENT_TIMESTAMP WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ii", $newQuantity, $row['id']);
    $updateStmt->execute();
    $updateStmt->close();
} else {
    // Insert new row
    $insertSql = "INSERT INTO cartitems (voucher_id, user_id, quantity, added_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("iii", $voucher_id, $user_id, $quantity);
    $insertStmt->execute();
    $insertStmt->close();
}
$stmt->close();

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
    "message" => "Item added to cart",
    "totalItems" => $totalItems
]);

$conn->close();
