<?php
include __DIR__ . "/../db.php";

$limit = 10; // 10 per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// total categories
$res = $conn->query("SELECT COUNT(*) AS total FROM categories");
$total = $res->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

// fetch for this page
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY category_id ASC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode([
    "categories" => $categories,
    "totalPages" => $totalPages
]);
?>