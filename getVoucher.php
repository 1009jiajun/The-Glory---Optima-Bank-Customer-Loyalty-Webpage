<?php
include "db.php";

header("Content-Type: application/json");

// Collect parameters
$limit       = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
$page        = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search      = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$category    = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : "";
$min_points  = isset($_GET['min_points']) ? intval($_GET['min_points']) : 0;
$max_points  = isset($_GET['max_points']) ? intval($_GET['max_points']) : 0;
$sort        = isset($_GET['sort']) ? $_GET['sort'] : "";

// Base WHERE clause
$where = "WHERE 1=1";

// Apply search
if (!empty($search)) {
    $where .= " AND (v.title LIKE '%$search%' OR v.description LIKE '%$search%')";
}

// Apply category
if (!empty($category)) {
    $where .= " AND c.name = '$category'";
}

// Apply point range
if ($max_points > 0) {
    $where .= " AND v.points BETWEEN $min_points AND $max_points";
}

// Count total (without limit)
$countSql = "SELECT COUNT(*) AS total
             FROM voucher v
             LEFT JOIN category c ON v.category_id = c.id
             $where";
$countResult = $conn->query($countSql);
$total = 0;
if ($countResult && $row = $countResult->fetch_assoc()) {
    $total = intval($row['total']);
}

// Main query
$sql = "SELECT v.id, v.category_id, c.name AS category, v.points, v.title, v.image, 
               v.description, v.terms_conditions, v.created_datetime, v.edited_datetime
        FROM voucher v
        LEFT JOIN category c ON v.category_id = c.id
        $where";

// Sorting
switch ($sort) {
    case "low":
        $sql .= " ORDER BY v.points ASC";
        break;
    case "high":
        $sql .= " ORDER BY v.points DESC";
        break;
    case "new":
        $sql .= " ORDER BY v.created_datetime DESC";
        break;
    default:
        $sql .= " ORDER BY v.edited_datetime DESC";
        break;
}

// Pagination with limit + page
if ($limit > 0) {
    $offset = ($page - 1) * $limit;
    $sql .= " LIMIT $offset, $limit";
}

$result = $conn->query($sql);

$vouchers = [];
while ($row = $result->fetch_assoc()) {
    $vouchers[] = $row;
}

// Final JSON response
echo json_encode([
    "data" => $vouchers,
    "total" => $total
]);

$conn->close();
?>
