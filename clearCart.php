<?php
// clearCart.php
header("Content-Type: application/json");
include "db.php";

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) {
  echo json_encode(["status"=>"error","message"=>"Invalid user_id"]);
  exit;
}

// Delete all items
$del = $conn->prepare("DELETE FROM cartitems WHERE user_id = ?");
$del->bind_param("i", $user_id);
$del->execute();
$del->close();

// Totals now 0
echo json_encode([
  "status" => "success",
  "message" => "Cart cleared",
  "totalItems" => 0,
  "totalPoints" => 0
]);

$conn->close();
