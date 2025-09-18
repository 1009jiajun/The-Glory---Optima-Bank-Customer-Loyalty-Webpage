<?php
include "db.php";

$user_id = $_GET['user_id'] ?? 0;
$search = $_GET['search'] ?? "";

$sql = "
SELECT h.id, h.voucher_id, h.code, h.quantity, h.completed_date,
       v.title, v.image, v.points AS points_used
FROM cartitemhistory h
JOIN voucher v ON h.voucher_id = v.id
WHERE h.user_id = ?
  AND (v.title LIKE ? OR h.code LIKE ?)
ORDER BY h.completed_date DESC
";

$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("iss", $user_id, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode($orders);
?>
