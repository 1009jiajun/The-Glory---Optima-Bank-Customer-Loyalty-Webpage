<?php
header("Content-Type: application/json");
include "db.php";

// --- Error Logging Setup ---
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/php-error.log"); // log file inside same folder
ini_set("display_errors", 0); // don't show errors to frontend

// --- Read incoming JSON ---
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["user_id"]) || !isset($data["items"])) {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
    exit;
}

$user_id = intval($data["user_id"]);
$items   = $data["items"];

try {
    $conn->begin_transaction();

    // Insert into history
    $insertStmt = $conn->prepare("INSERT INTO cartitemhistory (voucher_id, user_id, quantity, code, completed_date) VALUES (?, ?, ?, ?, NOW())");
    if (!$insertStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Delete from cart
    $deleteStmt = $conn->prepare("DELETE FROM cartitems WHERE voucher_id = ? AND user_id = ?");
    if (!$deleteStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Get voucher cost
    $voucherStmt = $conn->prepare("SELECT points FROM voucher WHERE id = ?");
    if (!$voucherStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $totalPointsToDeduct = 0;

    foreach ($items as $item) {
        if (!isset($item["voucher_id"], $item["quantity"], $item["code"])) {
            throw new Exception("Missing item data: " . json_encode($item));
        }

        $voucher_id = intval($item["voucher_id"]);
        $quantity   = intval($item["quantity"]);
        $code       = $item["code"];

        // --- Get voucher points_required ---
        $voucherStmt->bind_param("i", $voucher_id);
        $voucherStmt->execute();
        $voucherResult = $voucherStmt->get_result();

        if ($voucherResult->num_rows === 0) {
            throw new Exception("Voucher ID $voucher_id not found");
        }

        $voucherData = $voucherResult->fetch_assoc();
        $pointsRequired = intval($voucherData["points"]);

        $totalPointsToDeduct += $pointsRequired * $quantity;

        // Insert into history
        $insertStmt->bind_param("iiis", $voucher_id, $user_id, $quantity, $code);
        if (!$insertStmt->execute()) {
            throw new Exception("Insert failed: " . $insertStmt->error);
        }

        // Remove from cart
        $deleteStmt->bind_param("ii", $voucher_id, $user_id);
        if (!$deleteStmt->execute()) {
            throw new Exception("Delete failed: " . $deleteStmt->error);
        }
    }

    // --- Deduct points from user ---
    $updateUserStmt = $conn->prepare("UPDATE user SET points = points - ? WHERE id = ?");
    if (!$updateUserStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $updateUserStmt->bind_param("ii", $totalPointsToDeduct, $user_id);
    if (!$updateUserStmt->execute()) {
        throw new Exception("User points deduction failed: " . $updateUserStmt->error);
    }

    $conn->commit();
    echo json_encode(["status" => "success", "points_deducted" => $totalPointsToDeduct]);

} catch (Exception $e) {
    $conn->rollback();
    error_log($e->getMessage()); // log error into php-error.log
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn->close();
?>
